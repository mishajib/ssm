<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Auth\RegisterRequest;
use App\Http\Resources\CurrentUserResource;
use App\Http\Resources\UserListingResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['email_verified_at'] = now()->format('Y-m-d H:i:s');

        $data['password'] = Hash::make($data['password']);
        $data['customer_type_id'] = $request->get('customer_type');

        // Create User
        $user = User::create($data);

        // Generate token
        $token = $user->createToken('authToken')->plainTextToken;

        $user->load(['customerType']);

        return success_response(
            'User registered successfully.',
            [
                'user'       => new UserListingResource($user->refresh()),
                'token_type' => 'Bearer',
                'token'      => $token
            ],
            Response::HTTP_CREATED,
        );
    }
}
