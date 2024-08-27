<?php

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

uses(Tests\TestCase::class);



it('has student list page', function () {
    $teacher = User::where('email', 'teacher@app.com')->first();

    $response = $this->actingAs($teacher)->get('/api/v1/students');

    $response->assertStatus(200);
});

it('should be return desired per page data', function () {
    $teacher = User::where('email', 'teacher@app.com')->first();
    $perPage = 5;
    $response = $this->actingAs($teacher)->getJson('/api/v1/students?per_page=' . $perPage);
    expect($response->json('data.meta.per_page'))->toBe($perPage);
});
