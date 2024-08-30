<?php

use App\Http\Controllers\API\V1\Student\RateSessionController;
use App\Http\Controllers\API\V1\Teacher\Student\SessionsController;
use App\Http\Controllers\API\V1\Teacher\User\StudentsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    // Auth routes
    require __DIR__ . '/auth.php';

    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('teacher')->group(function () {
            // student routes
            Route::post('students/{student}/weekday-availability', [StudentsController::class, 'weekdayAvailability']);
            Route::apiResource('students', StudentsController::class);

            // Session routes
            Route::get('sessions', [SessionsController::class, 'index'])->name('sessions.index');
            Route::post('sessions', [SessionsController::class, 'store'])->name('sessions.store');
            Route::delete('sessions/{session}/destroy', [SessionsController::class, 'destroy'])->name('sessions.destroy');
        });

        Route::get('sessions/{session}', [SessionsController::class, 'show'])->name('sessions.show');

        Route::middleware('student')->group(function () {
            Route::post('rate-session/{session}', RateSessionController::class)->name('rate-session');
        });
    });
});