<?php

namespace App\Http\Controllers\API\V1\User;

use App\Actions\User\CreateOrUpdateUser;
use App\Http\Controllers\Controller;
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
            $students = User::student()
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
                null,
                $e->getCode(),
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
                null,
                $e->getCode(),
            );
        }
    }

    /**
     * Show Student
     */
    public function show(int $student): JsonResponse
    {
        try {
            $student = User::student()->find($student);

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
                Response::HTTP_CREATED,
            );
        } catch (Exception $e) {
            return error_response(
                'Something went wrong, please try again later!',
                null,
                $e->getCode(),
            );
        }
    }

    /**
     * Update Student
     */
    public function update(UserRequest $request, int $student): JsonResponse
    {
        try {
            $student = User::student()->find($student);

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
                Response::HTTP_CREATED,
            );
        } catch (Exception $e) {
            return error_response(
                'Something went wrong, please try again later!',
                null,
                $e->getCode(),
            );
        }
    }


    /**
     * Delete Student
     */
    public function destroy(int $student): JsonResponse
    {
        try {
            $student = User::student()->find($student);

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
                Response::HTTP_CREATED,
            );
        } catch (Exception $e) {
            return error_response(
                'Something went wrong, please try again later!',
                null,
                $e->getCode(),
            );
        }
    }
}
