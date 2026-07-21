<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Outgoing message moderation (§12.4)
    |--------------------------------------------------------------------------
    |
    | Keyword/pattern lists live in config (not hardcoded in the service class)
    | so they can be updated without a code deploy. This starter list mixes
    | English terms with a few common Twi/Ga/Ga terms as a placeholder — the
    | real list must be reviewed by a human moderation/linguistics team before
    | launch (§14.11). Never treat this as a complete or production-ready list.
    |
    | Each entry in `severe` triggers hold-before-delivery (message not sent to
    | recipient, held in the moderation queue). Each entry in `mild` triggers
    | deliver-and-flag (message is sent, but flagged for review).
    */

    'severe_keywords' => [
        // Threats / violence
        'kill you', 'i will kill', 'i will hurt', 'i will beat', 'gonna kill', 'beat you up',
        'rape you', 'come and die', 'destroy you',
        // Slurs (placeholder — expand with a real reviewed list before launch)
        'nigger', 'chink', 'kwasea fool',
    ],

    'mild_keywords' => [
        // Mild profanity
        'fuck', 'shit', 'bitch', 'asshole', 'bastard',
        // Contact-sharing (defense in depth — chat is already gated to mutual matches)
        'whatsapp me', 'my number is', 'call me on', 'add me on snap', 'add me on ig',
    ],

    // Regex patterns for contact info (phone numbers, emails, handles)
    'contact_patterns' => [
        '/\b0[2235]\d{8}\b/', // Ghanaian local phone format
        '/\+233\d{9}\b/',      // Ghanaian E.164 format
        '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', // email
        '/@[a-zA-Z0-9_.]{3,}/', // social handle
    ],

    'auto_suspend_after_flags' => (int) env('MATCH_MESSAGE_FLAG_ESCALATION_COUNT', 3),
];
