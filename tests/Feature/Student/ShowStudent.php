<?php

use App\Models\User;

uses(Tests\TestCase::class);


it('should return 404 when fetching a student that does not exist', function () {
    $user = User::where('email', 'teacher@app.com')->first();
    $response = $this->actingAs($user)->getJson("/api/v1/students/5000000");

    $response->assertStatus(404);
});

it('should return student details', function () {
    $user = User::where('email', 'teacher@app.com')->first();
    $student = User::where('email', 'student@app.com')->first();
    $response = $this->actingAs($user)->getJson("/api/v1/students/{$student->id}");

    $response->assertStatus(200);
});