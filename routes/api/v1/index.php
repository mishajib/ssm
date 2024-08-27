<?php

use App\Http\Controllers\API\V1\User\StudentsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    // Auth routes
    require __DIR__ . '/auth.php';

    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('teacher')->group(function () {
            // teacher routes
            Route::apiResource('students', StudentsController::class);
        });

        Route::middleware('student')->group(function () {
            // student routes
        });
    });
});