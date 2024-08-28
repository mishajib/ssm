<?php

namespace App\Http\Controllers\API\V1\User;

use App\Actions\User\CreateOrUpdateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\User\StudentAvailabilityRequest;
use App\Http\Requests\API\V1\User\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudentsController extends Controller
{
    /**
     * Student List
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 10);
            $searchQuery = $request->query('query');
            $students = User::ofStudent()
                ->when($searchQuery, function ($query, $searchQuery) {
                    return $query->where('name', 'LIKE', "%$searchQuery%");
                })
                ->paginate($perPage)->withQueryString();
            return success_response(
                'Students fetched successfully!',
                UserResource::collection($students)->response()->getData(true),
            );
        } catch (Exception $e) {
            return error_response(
                'Something went wrong, please try again later!',
            );
        }
    }

    /**
     * Create Student
     */
    public function store(UserRequest $request): JsonResponse
    {
        try {
            return success_response(
                'Student created successfully!',
                new UserResource(
                    (new CreateOrUpdateUser())->execute($request->validated())
                ),
                Response::HTTP_CREATED,
            );
        } catch (Exception $e) {
            return error_response(
                'Something went wrong, please try again later!',
            );
        }
    }

    /**
     * Show Student
     */
    public function show(int $studentId): JsonResponse
    {
        try {
            $student = User::ofStudent()->with(['student'])->find($studentId);

            if (! $student) {
                return error_response(
                    'Student not found!',
                    null,
                    Response::HTTP_NOT_FOUND,
                );
            }

            return success_response(
                'Student details fetched successfully!',
                new UserResource($student),
            );
        } catch (Exception $e) {
            return error_response(
                'Something went wrong, please try again later!',
            );
        }
    }

    /**
     * Update Student
     */
    public function update(UserRequest $request, int $studentId): JsonResponse
    {
        try {
            $student = User::ofStudent()->find($studentId);

            if (! $student) {
                return error_response(
                    'Student not found!',
                    null,
                    Response::HTTP_NOT_FOUND,
                );
            }

            return success_response(
                'Student updated successfully!',
                new UserResource(
                    (new CreateOrUpdateUser())->execute($request->validated(), $student->id)
                ),
            );
        } catch (Exception $e) {
            return error_response(
                'Something went wrong, please try again later!',
            );
        }
    }


    /**
     * Delete Student
     */
    public function destroy(int $studentId): JsonResponse
    {
        try {
            $student = User::ofStudent()->find($studentId);

            if (! $student) {
                return error_response(
                    'Student not found!',
                    null,
                    Response::HTTP_NOT_FOUND,
                );
            }

            $student->delete();
            return success_response(
                'Student updated successfully!',
                new UserResource($student),
            );
        } catch (Exception $e) {
            return error_response(
                'Something went wrong, please try again later!',
            );
        }
    }

    /**
     * Set Student Availability
     */
    public function weekdayAvailability(StudentAvailabilityRequest $request, int $studentId): JsonResponse
    {
        try {
            $student = User::ofStudent()->with(['student'])->find($studentId);

            if (! $student) {
                return error_response(
                    'Student not found!',
                    null,
                    Response::HTTP_NOT_FOUND,
                );
            }


            $student->student()->updateOrCreate(
                ['user_id' => $student->id],
                ['weekday_availability' => $request->get('week_days')]
            );

            return success_response(
                'Student weekday saved successfully!',
                new UserResource($student->refresh()),
            );
        } catch (Exception $e) {
            return error_response(
                $e->getMessage(),
            );
        }
    }
}
