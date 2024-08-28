<?php

use App\Models\User;

uses(Tests\TestCase::class);


it('should return validation error when setting student availability with invalid data', function () {
    $user = User::where('email', 'teacher@app.com')->first();
    $student = User::where('email', 'student@app.com')->first();
    $response = $this->actingAs($user)->postJson("/api/v1/students/{$student->id}/weekday-availability", [
        'week_days' => [
            // 1, // invalid week day
        ],
    ]);

    $response->assertStatus(422);
});

it('should return 500 any error occurs while setting student availability exclude validation', function () {
    $user = User::where('email', 'teacher@app.com')->first();
    $student = User::where('email', 'student@app.com')->first();
    $response = $this->actingAs($user)->postJson("/api/v1/students/{$student->id}/weekday-availability", [
        'week_days' => [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ],
    ]);

    if ($response->status() !== 422 && $response->status() !== 200) {
        $response->assertStatus(500);
        return;
    }

    $response->assertStatus(200);
});

it('can set student availability', function () {
    $user = User::where('email', 'teacher@app.com')->first();

    $student = User::where('email', 'student@app.com')->first();

    $response = $this->actingAs($user)->postJson("/api/v1/students/{$student->id}/weekday-availability", [
        'week_days' => [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ],
    ]);

    $response->assertStatus(200);
});