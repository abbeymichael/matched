<?php

namespace App\Services;

use App\Contracts\SmsProviderInterface;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class OtpService
{
    public function __construct(private readonly SmsProviderInterface $sms) {}

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0')) $digits = '233'.substr($digits, 1);
        if (! preg_match('/^233\d{9}$/', $digits)) {
            throw ValidationException::withMessages(['phone' => 'Enter a valid Ghana phone number.']);
        }
        return '+'.$digits;
    }

    public function issue(string $phone, string $purpose = 'login'): OtpCode
    {
        $plain = (string) random_int(100000, 999999);
        $otp = OtpCode::create([
            'phone' => $phone, 'code' => Hash::make($plain), 'purpose' => $purpose,
            'expires_at' => now()->addMinutes(config('matchlock.otp_expiry_minutes', 5)), 'attempts' => 0,
        ]);
        $this->sms->send($phone, "Your Synchrony code is {$plain}. It expires in 5 minutes.");
        return $otp;
    }

    public function verify(string $phone, string $plain): OtpCode
    {
        $otp = OtpCode::where('phone', $phone)->latest()->first();
        if (! $otp || $otp->expires_at->isPast() || $otp->attempts >= config('matchlock.otp_max_attempts', 5)) {
            throw ValidationException::withMessages(['code' => 'Invalid or expired code.']);
        }
        $otp->increment('attempts');
        if (! Hash::check($plain, $otp->code)) {
            throw ValidationException::withMessages(['code' => 'Invalid or expired code.']);
        }
        return $otp;
    }
}
