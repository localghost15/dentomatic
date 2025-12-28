<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\SettingController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

    // Doctors
    Route::apiResource('doctors', DoctorController::class);
    // POST for update to handle FormData easily if needed (Laravel sometimes has issues with PUT + FormData)
    Route::post('doctors/{doctor}', [DoctorController::class, 'update']);
    Route::post('doctors/{doctor}/generate-tg-code', [DoctorController::class, 'generateTgCode']);

    // Settings
    Route::get('settings', [SettingController::class, 'index']);
    Route::post('settings', [SettingController::class, 'update']);
});

// Telegram Webhook (Public)
Route::post('telegram/webhook', [\App\Http\Controllers\Api\TelegramWebhookController::class, 'handle']);
