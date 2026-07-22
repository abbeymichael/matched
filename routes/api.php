<?php

use App\Http\Controllers\Api\V1\Admin\FieldController as AdminFieldController;
use App\Http\Controllers\Api\V1\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Admin\VerificationController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\InterestController;
use App\Http\Controllers\Api\V1\LockController;
use App\Http\Controllers\Api\V1\MatchController;
use App\Http\Controllers\Api\V1\PreferenceController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/otp/send', [AuthController::class, 'sendOtp']);
    Route::post('/otp/verify', [AuthController::class, 'verify']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/fields', [ProfileController::class, 'fields']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/profile', [ProfileController::class, 'store']);
        Route::get('/preferences', [PreferenceController::class, 'show']);
        Route::post('/preferences', [PreferenceController::class, 'store']);
        Route::post('/lock', [LockController::class, 'lock']);
        Route::post('/reset', [SettingsController::class, 'reset']);
        Route::patch('/threshold', [SettingsController::class, 'threshold']);

        Route::get('/matches', [MatchController::class, 'index']);
        Route::get('/matches/{user}', [MatchController::class, 'show']);
        Route::post('/matches/{user}/interest', [InterestController::class, 'store']);

        Route::get('/chat', [ChatController::class, 'threads']);
        Route::get('/chat/{match}', [ChatController::class, 'messages']);
        Route::post('/chat/{match}', [ChatController::class, 'send']);

        Route::post('/report', [ReportController::class, 'store']);

        Route::middleware('can:admin')->prefix('admin')->group(function () {
            Route::get('/fields', [AdminFieldController::class, 'index']);
            Route::patch('/fields/{field}', [AdminFieldController::class, 'update']);
            Route::get('/fields/{field}/options', [AdminFieldController::class, 'options']);
            Route::patch('/fields/{field}/options/{option}', [AdminFieldController::class, 'updateOption']);
            Route::get('/reports', [AdminReportController::class, 'index']);
            Route::get('/reports/{report}', [AdminReportController::class, 'show']);
            Route::post('/reports/{report}', [AdminReportController::class, 'review']);
            Route::get('/users', [AdminUserController::class, 'index']);
            Route::patch('/users/{user}', [AdminUserController::class, 'update']);
            Route::get('/verification', [VerificationController::class, 'index']);
            Route::post('/verification/{user}', [VerificationController::class, 'review']);
        });
    });
});
