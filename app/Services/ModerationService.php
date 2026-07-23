<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;

/**
 * Scan outgoing messages and other user content for safety signals.
 *
 * Implements a two-tier moderation policy (§12.4):
 *   - SEVERE: hold-before-delivery. Message is not delivered to the recipient.
 *   - MILD / contact patterns: deliver-and-flag. Message is sent but flagged for review.
 *
 * The lists are driven by config/moderation.php so they can be updated without
 * a redeploy. This is a defense-in-depth layer, not a replacement for human review.
 */
class ModerationService
{
    /**
     * Check message content and return a moderation result.
     *
     * @return array{flagged: bool, severe: bool, reasons: list<string>}
     */
    public function checkMessage(string $body): array
    {
        $text = mb_strtolower($body, 'UTF-8');
        $reasons = [];
        $severe = false;

        foreach ((array) config('moderation.severe_keywords', []) as $keyword) {
            if (str_contains($text, mb_strtolower($keyword, 'UTF-8'))) {
                $reasons[] = 'Severe keyword: ' . $keyword;
                $severe = true;
            }
        }

        foreach ((array) config('moderation.mild_keywords', []) as $keyword) {
            if (str_contains($text, mb_strtolower($keyword, 'UTF-8'))) {
                $reasons[] = 'Mild keyword: ' . $keyword;
            }
        }

        foreach ((array) config('moderation.contact_patterns', []) as $pattern) {
            if (preg_match($pattern, $body)) {
                $reasons[] = 'Contact pattern: ' . $pattern;
            }
        }

        $reasons = array_values(array_unique($reasons));

        return [
            'flagged' => $severe || ! empty($reasons),
            'severe' => $severe,
            'reasons' => $reasons,
        ];
    }

    /**
     * Determine whether a user should be auto-suspended based on their recent flag history.
     */
    public function shouldEscalate(User $user): bool
    {
        $threshold = (int) config('moderation.auto_suspend_after_flags', 3);

        if ($threshold <= 0) {
            return false;
        }

        $recentFlagCount = Message::query()
            ->where('sender_id', $user->id)
            ->where('flagged', true)
            ->where('sent_at', '>=', now()->subDays(30))
            ->count();

        return $recentFlagCount >= $threshold;
    }
}
