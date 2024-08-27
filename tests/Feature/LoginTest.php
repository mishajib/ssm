<?php

use App\Models\User;

uses(Tests\TestCase::class);

describe('LoginTest', function () {
    test('should return 200 when login with correct credentials', function () {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'teacher@app.com',
            'password' => 'password',
        ]);


        // also check if the response has success key and its value is true
        $response->assertStatus(200)
            ->assertJsonStructure(['success'])
            ->assertJson(['success' => true]);
    });

    test('should return 422 when login with incorrect credentials', function () {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'invalid@app.com',
            'password' => 'invalid',
        ]);

        $response->assertStatus(422);
    });
});

describe('CurrentUserTest', function () {
    test('should return 200 when get current user', function () {
        $user = User::where('email', 'teacher@app.com')->first();

        $response = $this->actingAs($user)->getJson('/api/v1/user');

        // check also if the response has data key and its also has user key
        $response->assertStatus(\Illuminate\Http\Response::HTTP_OK)
            ->assertJsonStructure(['data'])
            ->assertJsonStructure(['data' => ['user']])
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email']]]);
    });

    test('should return 401 when get current user without token or invalid token', function () {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
    });
});

describe('LogoutTest', function () {
    test('should return 200 when logged out', function () {
        $user = User::where('email', 'teacher@app.com')->first();

        $response = $this->actingAs($user)->postJson('/api/v1/logout');

        // check also if the response has data key and its also has user key
        $response->assertStatus(200);
    });

    test('should return 401 when logged out without token or invalid token', function () {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    });
});