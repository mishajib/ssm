<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    public function codeCheck(Request $request)
    {
        $request->validate([
            'code' => 'bail|required|string|exists:password_reset_codes,code',
        ]);

        // find the code
        $passwordReset = PasswordResetCode::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if (Carbon::parse($passwordReset->created_at) > now()->addMinutes(config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'))) {
            $passwordReset->delete();
            return error_response(
                'Your code is expired, please request a new one.',
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return success_response(
            'Your code is valid.',
            [
                'code' => $passwordReset->code
            ]
        );
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'bail|required|string|exists:password_reset_codes,code',
            'password' => ['bail', 'required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['bail', 'required', Rules\Password::defaults()],
        ]);

        // find the code
        $passwordReset = PasswordResetCode::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if (Carbon::parse($passwordReset->created_at) > now()->addMinutes(config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'))) {
            $passwordReset->delete();
            return error_response(
                'Your code is expired, please request a new one.',
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // find user's email
        $user = User::firstWhere('email', $passwordReset->email);

        // update user password
        $user->update([
            'password' => bcrypt($request->password),
            'set_password_after_social_login' => 1,
        ]);

        // delete current code
        $passwordReset->delete();

        return success_response(
            'Your password has been reset successfully.',
        );
    }
}
