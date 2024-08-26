<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\Auth\LoginController;

Route::post('login', [LoginController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [LoginController::class, 'currentUser']);
    Route::post('logout', [LoginController::class, 'logout']);
});
