<?php

namespace App\Actions\Student\Session;

use App\Jobs\SessionRepetitionJob;
use App\Models\Session;
use App\Models\User;
use App\Notifications\SessionReminderNotification;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

final class CreateSession
{
    /**
     * Create a new session for a student
     *
     * @throws Exception
     */
    public function execute(array $data)
    {
        DB::beginTransaction();
        try {
            // Fetch student availability
            $studentId = $data['student_id'];
            $user = $this->getUser($studentId);
            $availabilities = $user->student?->weekday_availability;

            // Parse the start and end times and check if they are within available days
            [$startTime, $endTime] = $this->getParsedTimes($data['start_time'], $data['end_time'], $availabilities);

            // Check if there is available time left for scheduling a session today
            $this->checkScheduleTimeAvailability($startTime, $endTime, $studentId);

            // Check for overlapping sessions
            $this->checkOverlapExists($studentId, $startTime, $endTime);

            // Ensure session duration does not exceed 15 minutes
            $this->checkDuration($startTime, $endTime);

            // Create the session
            $session = Session::create([
                'student_id' => $studentId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'repeat' => $data['repeat'] ?? false,
            ]);

            // Schedule notifications
            $this->scheduleNotifications($session);

            // Repeat the session if needed
            if ($session->repeat) {
                // Dispatch the job to handle the next repetition (this job dispatch before the session start)
                $nextStartTime = Carbon::parse($session->start_time)->addDay()->subMinutes(30);
                SessionRepetitionJob::dispatch($session)->delay($nextStartTime);
            }

            DB::commit();
            return $session;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    private function getUser($studentId): User
    {
        $user = User::find($studentId);

        if (!$user) {
            throw ValidationException::withMessages([
                'student_id' => 'Student not found!',
            ]);
        }

        return $user;
    }

    private function getParsedTimes($startTime, $endTime, $availabilities): array
    {
        $startTime = Carbon::parse($startTime);
        $endTime = Carbon::parse($endTime);

        // Check if the session day is within available days
        if (!in_array($startTime->format('l'), $availabilities)) {
            throw ValidationException::withMessages([
                'start_time' => 'Session day is not within student availability!',
            ]);
        }

        return [$startTime, $endTime];
    }

    private function checkScheduleTimeAvailability($startTime, $endTime, $studentId): void
    {
        // Calculate total scheduled time for the day
        $dayStart = $startTime->copy()->startOfDay();
        $dayEnd = $startTime->copy()->endOfDay();

        $scheduledMinutes = Session::where('student_id', $studentId)
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->sum(DB::raw('TIMESTAMPDIFF(MINUTE, start_time, end_time)'));

        $remainingMinutes = 15 - $scheduledMinutes;

        if ($remainingMinutes <= 0) {
            throw ValidationException::withMessages([
                'start_time' => 'No available time left for scheduling a session today!',
            ]);
        }

        // Ensure the new session doesn't exceed the 15-minute limit
        $newSessionMinutes = $startTime->diffInMinutes($endTime);

        if ($newSessionMinutes > $remainingMinutes) {
            throw ValidationException::withMessages([
                'end_time' => "Only $remainingMinutes minute(s) left for scheduling a session today!",
            ]);
        }
    }

    private function checkOverlapExists($studentId, $startTime, $endTime): void
    {
        $overlapExists = Session::where('student_id', $studentId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime]);
            })
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'start_time' => 'Session overlaps with existing one!',
                'end_time' => 'Session overlaps with existing one!',
            ]);
        }
    }

    private function checkDuration($startTime, $endTime): void
    {
        if ($startTime->diffInMinutes($endTime) > 15) {
            throw ValidationException::withMessages([
                'end_time' => 'Session duration exceeds 15 minutes!',
            ]);
        }
    }

    private function scheduleNotifications(Session $session): void
    {
        $reminderTime = Carbon::parse($session->start_time)->subMinutes(5);
        // Schedule the notification (This assumes you have a queue system set up)
        Notification::route('mail', $session->student->email)
            ->notify((new SessionReminderNotification($session))->delay($reminderTime));
    }
}