<?php

use App\Livewire\Auth\OtpVerification;
use App\Livewire\Auth\PhoneEntry;
use App\Livewire\Chat\Thread;
use App\Livewire\Chat\ThreadList;
use App\Livewire\Dashboard\MatchList;
use App\Livewire\Dashboard\ProfileDetail;
use App\Livewire\Onboarding\PhotoUpload;
use App\Livewire\Onboarding\PreferenceWizard;
use App\Livewire\Onboarding\ProfileWizard;
use App\Livewire\Onboarding\ReviewAndLock;
use App\Livewire\Onboarding\SelfieUpload;
use App\Livewire\Settings\AccountSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', PhoneEntry::class)->name('login');
    Route::get('/login/verify', OtpVerification::class)->name('otp.show');
});

Route::middleware(['auth', 'not.banned', 'verified', 'track.active'])->group(function () {
    Route::get('/onboarding/photos', PhotoUpload::class)->name('onboarding.photos');
    Route::get('/onboarding/selfie', SelfieUpload::class)->name('onboarding.selfie');
    Route::get('/onboarding/profile', ProfileWizard::class)->name('onboarding.profile');
    Route::get('/onboarding/preferences', PreferenceWizard::class)->name('onboarding.preferences');
    Route::get('/onboarding/review', ReviewAndLock::class)->name('onboarding.review');
    Route::get('/verification-pending', fn () => view('pages.verification-pending'))->name('verification.pending');
});

Route::middleware(['auth', 'not.banned', 'verified', 'verified.identity', 'locked', 'track.active'])->group(function () {
    Route::get('/dashboard', MatchList::class)->name('dashboard');
    Route::get('/matches/{user}', ProfileDetail::class)->name('matches.show');
    Route::get('/chat', ThreadList::class)->name('chat.index');
    Route::get('/chat/{match}', Thread::class)->name('chat.show');
    Route::get('/settings', AccountSettings::class)->name('settings');
});

Route::middleware(['auth', 'not.banned'])->group(function () {
    Route::get('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});

Route::view('/banned', 'pages.banned')->name('banned');
Route::view('/under-review', 'pages.under-review')->name('under-review');
Route::view('/offline', 'pages.offline')->name('offline');

Route::middleware(['auth', 'can:admin'])->prefix('admin')->group(function () {
    Route::get('/fields', App\Livewire\Admin\Fields\FieldManager::class)->name('admin.fields');
    Route::get('/fields/{field}/options', App\Livewire\Admin\Fields\OptionManager::class)->name('admin.fields.options');
    Route::get('/reports', App\Livewire\Admin\Reports\ReportQueue::class)->name('admin.reports');
    Route::get('/reports/{report}', App\Livewire\Admin\Reports\ReportDetail::class)->name('admin.reports.show');
    Route::get('/users', App\Livewire\Admin\Users\UserList::class)->name('admin.users');
    Route::get('/verification', App\Livewire\Admin\Verification\VerificationQueue::class)->name('admin.verification');
});
