<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;

final class VerifyOtp
{
    public function __construct(private readonly OtpService $otps) {}

    public function handle(string $rawPhone, string $code): User
    {
        $phone = $this->otps->normalizePhone($rawPhone);
        $this->otps->verify($phone, $code);
        $user = User::firstOrCreate(['phone' => $phone], [
            'phone_verified_at' => now(), 'status' => 'active', 'verification_status' => 'pending',
            'match_threshold' => config('matchlock.default_match_threshold', 60),
        ]);
        $user->forceFill(['phone_verified_at' => now()])->save();
        Auth::login($user, remember: true);
        request()->session()->regenerate();
        return $user;
    }
}
