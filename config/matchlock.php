<?php

return [
    // OTP / auth
    'otp_expiry_minutes' => (int) env('OTP_EXPIRY_MINUTES', 5),
    'otp_max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    'otp_length' => (int) env('OTP_LENGTH', 6),
    'otp_max_requests_per_phone' => (int) env('OTP_MAX_REQUESTS_PER_PHONE', 3),
    'otp_request_window_minutes' => (int) env('OTP_REQUEST_WINDOW_MINUTES', 15),
    'otp_max_requests_per_ip' => (int) env('OTP_MAX_REQUESTS_PER_IP', 10),

    // Matching
    'default_match_threshold' => (int) env('MATCH_DEFAULT_THRESHOLD', 60),
    'geo_decay_multiplier' => (float) env('MATCH_GEO_DECAY_MULTIPLIER', 1.5),
    'range_buffer_percent' => (float) env('MATCH_RANGE_BUFFER_PERCENT', 0.20),
    'range_buffer_minimum' => (int) env('MATCH_RANGE_BUFFER_MINIMUM', 2),

    // Minimum age requirement (§13.1)
    'minimum_age' => (int) env('MATCH_MINIMUM_AGE', 18),

    // Moderation escalation thresholds (§12)
    'standard_report_escalation_count' => (int) env('MATCH_STANDARD_REPORT_ESCALATION_COUNT', 3),
    'message_flag_escalation_count' => (int) env('MATCH_MESSAGE_FLAG_ESCALATION_COUNT', 3),

    // SMS
    'sms_provider' => env('SMS_PROVIDER', 'log'),

    // Images (§8.5)
    'image_max_width' => (int) env('IMAGE_MAX_WIDTH', 1200),
    'image_quality' => (int) env('IMAGE_QUALITY', 75),
    'image_disk' => env('IMAGE_DISK', 'public'),
    'max_profile_photos' => 4,

    // Ghana phone prefix
    'country_code' => '233',
];
