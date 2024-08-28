<?php

use App\Http\Controllers\API\V1\User\StudentsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    // Auth routes
    require __DIR__ . '/auth.php';

    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('teacher')->group(function () {
            // student routes
            Route::post('students/{student}/weekday-availability', [StudentsController::class, 'weekdayAvailability']);
            Route::apiResource('students', StudentsController::class);
        });

        Route::middleware('student')->group(function () {
            // student routes
        });
    });
});