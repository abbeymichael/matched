<?php

namespace App\Services;

/**
 * Outgoing-message content check (§12.4). Keyword/pattern lists live in
 * config/moderation.php, not hardcoded here, so they can be updated without
 * a code deploy. Severe matches (threats, slurs) => hold before delivery.
 * Mild matches (profanity, contact-sharing) => deliver but flag for review.
 */
final class ModerationService
{
    public function check(string $text): ModerationResult
    {
        $normalized = mb_strtolower($text);

        foreach (config('moderation.severe_keywords', []) as $keyword) {
            if ($this->contains($normalized, $keyword)) {
                return new ModerationResult(flagged: true, deliver: false, reason: 'keyword_match:severe');
            }
        }

        foreach (config('moderation.contact_patterns', []) as $pattern) {
            if (@preg_match($pattern, $text) === 1) {
                return new ModerationResult(flagged: true, deliver: true, reason: 'pattern_match:contact_info');
            }
        }

        foreach (config('moderation.mild_keywords', []) as $keyword) {
            if ($this->contains($normalized, $keyword)) {
                return new ModerationResult(flagged: true, deliver: true, reason: 'keyword_match:mild');
            }
        }

        return new ModerationResult(flagged: false, deliver: true, reason: null);
    }

    private function contains(string $haystackNormalized, string $needle): bool
    {
        return mb_strpos($haystackNormalized, mb_strtolower($needle)) !== false;
    }
}
