<?php

namespace App\Services;

use App\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

final class LogSmsProvider implements SmsProviderInterface
{
    public function send(string $phone, string $message): void
    {
        Log::channel('single')->info('Synchrony OTP dispatched', [
            'phone' => substr($phone, 0, 7).'•••',
            'message' => $message,
        ]);
    }
}
