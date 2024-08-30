<?php

namespace App\Actions\Student\Session;

use App\Jobs\SessionRepetitionJob;
use App\Models\Session;
use App\Notifications\SessionReminderNotification;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

final class RepeatSession
{
    /**
     * Create a new session for a student
     *
     * @throws Exception
     */
    public function execute(Session $session): Session
    {
        DB::beginTransaction();
        try {
            $nextStartTime = Carbon::parse($session->start_time)->addDay();
            $nextEndTime = Carbon::parse($session->end_time)->addDay();
            $studentId = $session->student_id;
            // Fetch student availability
            $availabilities = $session->student?->student?->weekday_availability;

            // Parse the start and end times and check if they are within available days
            [$startTime, $endTime] = $this->getParsedTimes($nextStartTime, $nextEndTime, $availabilities);

            // Check if there is available time left for scheduling a session today
            $this->checkScheduleTimeAvailability($startTime, $endTime, $studentId);

            // Check for overlapping sessions
            $this->checkOverlapExists($studentId, $startTime, $endTime);

            // Ensure session duration does not exceed 15 minutes
            $this->checkDuration($startTime, $endTime);

            // Re-create the session
            $newSession = $session->replicate();
            $newSession->start_time = $startTime;
            $newSession->end_time = $endTime;
            $newSession->save();

            // Schedule notifications
            $this->scheduleNotifications($session);

            // Dispatch the job to handle the next repetition
            if ($newSession->repeat) {
                $nextStartTime = Carbon::parse($session->start_time)->addDay()->subMinutes(30);
                SessionRepetitionJob::dispatch($newSession)->delay($nextStartTime);
            }

            DB::commit();
            return $newSession;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('RepeatSession@execute :: ', ['error' => $exception]);
            throw $exception;
        }
    }

    private function getParsedTimes($startTime, $endTime, $availabilities): array
    {
        $startTime = Carbon::parse($startTime);
        $endTime = Carbon::parse($endTime);

        // Check if the session day is within available days
        if (!in_array($startTime->format('l'), $availabilities)) {
            throw new ValidationException('Session day is not within student availability!');
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
            throw new ValidationException('No available time left for scheduling a session today!');
        }

        // Ensure the new session doesn't exceed the 15-minute limit
        $newSessionMinutes = $startTime->diffInMinutes($endTime);

        if ($newSessionMinutes > $remainingMinutes) {
            throw new ValidationException("Only $remainingMinutes minute(s) left for scheduling a session today!");
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
            throw new ValidationException('Session overlaps with existing one!');
        }
    }

    private function checkDuration($startTime, $endTime): void
    {
        if ($startTime->diffInMinutes($endTime) > 15) {
            throw new ValidationException('Session duration exceeds 15 minutes!');
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