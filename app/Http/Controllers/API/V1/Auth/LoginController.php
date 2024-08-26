<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class LoginController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $request->user()->tokens()->delete();
        $token = $request->user()->createToken('authToken')->plainTextToken;

        return success_response(
            "Login successful!",
            [
                'user' => (new UserResource($request->user()))->resolve(),
                'token_type' => 'Bearer',
                'token' => $token,
            ]
        );

    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return success_response(
            'Logged out successfully!'
        );
    }

    public function currentUser(Request $request): JsonResponse
    {
        return success_response(
            'User found!',
            [
                'user' => new UserResource($request->user())
            ]
        );
    }

}
