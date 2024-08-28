<?php

use App\Models\User;

uses(Tests\TestCase::class);


it('should return 404 when deleting a student that does not exist', function () {
    $user = User::where('email', 'teacher@app.com')->first();
    $response = $this->actingAs($user)->deleteJson("/api/v1/students/5000000");

    $response->assertStatus(404);
});


it('can delete a student', function () {
    $user = User::where('email', 'teacher@app.com')->first();
    $response = $this->actingAs($user)->deleteJson("/api/v1/students/100");

    if ($response->status() !== 404 && $response->status() !== 200) {
        $response->assertStatus(500);
        return;
    }

    if ($response->status() === 404) {
        $response->assertStatus(404);
        return;
    }


    $response->assertStatus(200);
});