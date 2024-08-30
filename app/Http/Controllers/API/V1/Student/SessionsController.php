<?php

namespace App\Http\Controllers\API\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Student\SessionRequest;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SessionsController extends Controller
{
    /**
     * Session List
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $searchQuery = $request->query('query');
            $sessions = Session::query()
                ->with('student')
                ->when($searchQuery, function ($query, $searchQuery) {
                    return $query->whereHas('student', function ($query) use ($searchQuery) {
                        $query->where('name', 'LIKE', "%$searchQuery%");
                    });
                })
                ->paginate($perPage)->withQueryString();
            return success_response(
                'Sessions fetched successfully!',
                SessionResource::collection($sessions)->response()->getData(true),
            );
        } catch (Exception $e) {
            Log::error('SessionsController@index', ['message' => $e->getMessage()]);
            return error_response(
                'Something went wrong, please try again later!',
            );
        }
    }

    /**
     * Create Session
     */
    public function store(SessionRequest $request)
    {
        try {
            $studentId = $request->student_id;
            $user = User::find($studentId);
            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);

            // Fetch student availability
            $availabilities = $user->student?->weekday_availability;

            // Check if the session day is within available days
            if (!in_array($startTime->format('l'), $availabilities)) {
                return response()->json(['error' => 'Selected day is not within available days'], 422);
            }

            // Check for overlapping sessions
            $overlapExists = Session::where('student_id', $studentId)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime]);
                })
                ->exists();

            if ($overlapExists) {
                return response()->json(['error' => 'Session overlaps with existing one'], 422);
            }
        } catch (Exception $e) {
            Log::error('SessionsController@store', ['message' => $e->getMessage()]);
            return error_response(
                'Something went wrong, please try again later!',
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
