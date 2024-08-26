<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Mail\APIResetPasswordMail;
use App\Models\PasswordResetCode;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'bail|required|email|exists:users',
        ]);


        // add throttle here, check if there is a code for this email in the last 2 minutes
        $code = PasswordResetCode::whereEmail($request->email)->first();
        if ($code && Carbon::parse($code->created_at)->diffInMinutes(now()) < 2) {
            throw ValidationException::withMessages([
                'email' => [__('auth.throttle', ['seconds' => 120 - Carbon::parse($code->created_at)->diffInSeconds(now())])],
            ]);
        }

        // Delete all previous code for this email
        PasswordResetCode::whereEmail($request->email)->delete();

        // generate a new code
        $data['code']       = mt_rand(100000, 999999);
        $data['created_at'] = now();

        // save the code
        $codeData = PasswordResetCode::create($data);

        // Send the email
        Mail::to($request->email)->send(new APIResetPasswordMail($codeData));

        return success_response(
            __('passwords.sent')
        );
    }
}
