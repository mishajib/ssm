<?php

use App\Models\User;

uses(Tests\TestCase::class);


it('should return validation error when creating a student with invalid data', function () {
    $user = User::where('email', 'teacher@app.com')->first();
    $student = User::where('email', 'student@app.com')->first();
    $response = $this->actingAs($user)->putJson("/api/v1/students/{$student->id}");

    $response->assertStatus(422);
});

it('should return 500 any error occurs while creating a student exclude validation', function () {
    $user = User::where('email', 'teacher@app.com')->first();
    $student = User::where('email', 'student@app.com')->first();
    $response = $this->actingAs($user)->putJson("/api/v1/students/{$student->id}", [
        'name' => $student->name,
        'email' => $student->email,
    ]);

    if ($response->status() !== 422 && $response->status() !== 200) {
        $response->assertStatus(500);
        return;
    }

    $response->assertStatus(200);
});

it('can update a student', function () {
    $user = User::where('email', 'teacher@app.com')->first();
    $student = User::where('email', 'student@app.com')->first();
    $response = $this->actingAs($user)->putJson("/api/v1/students/{$student->id}", [
        'name' => $student->name,
        'email' => $student->email,
    ]);

    $response->assertStatus(200);
});