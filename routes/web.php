<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard.index')->name('dashboard');
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.login')->name('register');
Route::post('/login/otp', [AuthController::class, 'sendOtp'])->name('otp.send');
Route::view('/login/verify', 'auth.verify')->name('otp.show');
Route::post('/login/verify', [AuthController::class, 'verify'])->name('otp.verify');
