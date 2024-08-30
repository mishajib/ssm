<?php

namespace App\Http\Controllers\API\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\SessionResource;
use App\Models\Session;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RateSessionController extends Controller
{
    /**
     * Rate Session
     */
    public function __invoke(Request $request, string $sessionId)
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
}
