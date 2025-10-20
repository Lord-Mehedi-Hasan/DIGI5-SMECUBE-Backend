<?php

use App\Http\Controllers\OtpController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
    Route::post('/send-otp', [OtpController::class, 'sendOtp'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});
