<?php

return [
    'otp_expiry_minutes' => (int) env('OTP_EXPIRY_MINUTES', 5),
    'otp_max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    'default_match_threshold' => (int) env('MATCH_DEFAULT_THRESHOLD', 60),
    'sms_provider' => env('SMS_PROVIDER', 'log'),
];
