<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(Tests\TestCase::class);
uses(DatabaseTransactions::class)->in('Feature');

describe('LoginTest', function () {
    test('should return 200 when login with correct credentials', function () {

        $user = \App\Models\User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);


        // also check if the response has success key and its value is true
        $response->assertStatus(\Illuminate\Http\Response::HTTP_OK)
            ->assertJsonStructure(['success'])
            ->assertJson(['success' => true]);
    });

    test('should return 422 when login with incorrect credentials', function () {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'invalid@app.com',
            'password' => 'invalid',
        ]);

        $response->assertStatus(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
    });
});

describe('CurrentUserTest', function () {
    test('should return 200 when get current user', function () {
        $user = \App\Models\User::factory()->create();
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $tokenType = $loginResponse->json('data.token_type');
        $token = $loginResponse->json('data.token');

        $response = $this->getJson('/api/v1/user', [
            'Authorization' => $tokenType . ' ' . $token,
        ]);

        // check also if the response has data key and its also has user key
        $response->assertStatus(\Illuminate\Http\Response::HTTP_OK)
            ->assertJsonStructure(['data'])
            ->assertJsonStructure(['data' => ['user']])
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email']]]);
    });

    test('should return 401 when get current user without token or invalid token', function () {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(\Illuminate\Http\Response::HTTP_UNAUTHORIZED);
    });
});

describe('LogoutTest', function () {
    test('should return 200 when logged out', function () {
        $user = \App\Models\User::factory()->create();
        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $tokenType = $loginResponse->json('data.token_type');
        $token = $loginResponse->json('data.token');

        $response = $this->postJson('/api/v1/logout', [], [
            'Authorization' => $tokenType . ' ' . $token,
        ]);

        // check also if the response has data key and its also has user key
        $response->assertStatus(\Illuminate\Http\Response::HTTP_OK);
    });

    test('should return 401 when logged out without token or invalid token', function () {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(\Illuminate\Http\Response::HTTP_UNAUTHORIZED);
    });
});