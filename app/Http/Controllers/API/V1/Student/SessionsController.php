<?php

namespace App\Http\Controllers\API\V1\Student;

use App\Actions\Student\Session\CreateSession;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Student\SessionRequest;
use App\Http\Resources\SessionResource;
use App\Http\Resources\UserResource;
use App\Models\Session;
use App\Models\Student;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
            $session = app(CreateSession::class)->execute($request->validated());

            return response()->json($session, 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('SessionsController@store', ['message' => $e->getMessage()]);
            return error_response(
                'Something went wrong, please try again later!',
                [
                    'errors' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $sessionId)
    {
        try {
            $session = Session::with(['student', 'teacher'])->find($sessionId);

            if (! $session) {
                return error_response(
                    'Session not found!',
                    null,
                    Response::HTTP_NOT_FOUND,
                );
            }

            return success_response(
                'Session details fetched successfully!',
                new SessionResource($session),
            );
        } catch (Exception $e) {
            return error_response(
                'Something went wrong, please try again later!',
            );
        }
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
