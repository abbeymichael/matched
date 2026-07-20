<?php

namespace App\Actions\Auth;

use App\Services\OtpService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final class SendOtp
{
    public function __construct(private readonly OtpService $otps) {}

    public function handle(string $rawPhone): string
    {
        $phone = $this->otps->normalizePhone($rawPhone);
        $key = 'otp:'.$phone;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages(['phone' => 'Please wait before requesting another code.']);
        }
        RateLimiter::hit($key, 900);
        $this->otps->issue($phone);
        return $phone;
    }
}
