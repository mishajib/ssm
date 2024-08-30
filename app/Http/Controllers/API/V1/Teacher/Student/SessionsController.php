<?php

namespace App\Http\Controllers\API\V1\Teacher\Student;

use App\Actions\Student\Session\CreateSession;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Student\SessionRequest;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
     * Schedule/Create Session
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
     * Session Details
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
     * Rate Session
     */
    public function update(Request $request, string $sessionId)
    {
        $request->validate([
            'rating' => 'required|numeric|min:1|max:10',
        ]);

        DB::beginTransaction();
        try {
            $session = Session::find($sessionId);
            if (! $session) {
                return error_response(
                    'Session not found!',
                    null,
                    Response::HTTP_NOT_FOUND,
                );
            }

            $session->update([
                'rating' => $request->get('rating'),
            ]);

            DB::commit();

            return success_response(
                'Session rated successfully!',
                new SessionResource($session),
            );
        } catch (Exception $e) {
            DB::rollBack();

            return error_response(
                'Something went wrong, please try again later!',
            );
        }
    }

    /**
     * Delete Session
     */
    public function destroy(int $sessionId)
    {
        try {
            $session = Session::with(['teacher', 'student'])->find($sessionId);

            if (! $session) {
                return error_response(
                    'Session not found!',
                    null,
                    Response::HTTP_NOT_FOUND,
                );
            }

            $session->delete();
            return success_response(
                'Session delete successfully!',
                new SessionResource($session),
            );
        } catch (Exception $e) {
            return error_response(
                'Something went wrong, please try again later!',
            );
        }
    }
}
