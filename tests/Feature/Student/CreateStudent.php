<?php

use App\Models\User;

uses(Tests\TestCase::class);


it('should return validation error when creating a student with invalid data', function () {
    $user = User::where('email', 'teacher@app.com')->first();
    $response = $this->actingAs($user)->postJson('/api/v1/students', [
        'name' => 'Test Student',
        // 'email' => 'teststudent@test.com',
        'password' => 'password',
        // 'password_confirmation' => 'password',
    ]);

    $response->assertStatus(422);
});

it('should return 500 any error occurs while creating a student exclude validation', function () {
    $user = User::where('email', 'teacher@app.com')->first();
    $response = $this->actingAs($user)->postJson('/api/v1/students', [
        'name' => 'Test Student',
        'email' => 'teststudent@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    User::where('email', 'teststudent@test.com')->delete();

    if ($response->status() !== 422 && $response->status() !== 201) {
        $response->assertStatus(500);
        return;
    }

    $response->assertStatus(201);
});

it('can create a student', function () {
    $user = User::where('email', 'teacher@app.com')->first();

    $response = $this->actingAs($user)->postJson('/api/v1/students', [
        'name' => 'Test Student',
        'email' => 'teststudent@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201);
});

it('should return email already taken error when creating a student with existing email', function () {
    $user = User::where('email', 'teacher@app.com')->first();

    $response = $this->actingAs($user)->postJson('/api/v1/students', [
        'name' => 'Test Student',
        'email' => 'teststudent@test.com',
    ]);

    $response->assertStatus(422);
});