<?php

namespace Tests\Unit\Services;

use App\Services\ModerationService;
use Tests\TestCase;

class ModerationServiceTest extends TestCase
{
    public function test_clean_message_is_delivered_and_not_flagged(): void
    {
        $service = new ModerationService();
        $result = $service->check('Hey, how was your weekend?');

        $this->assertFalse($result->flagged);
        $this->assertTrue($result->deliver);
    }

    public function test_severe_keyword_is_held_not_delivered(): void
    {
        $severe = config('moderation.severe_keywords')[0] ?? null;
        if (! $severe) {
            $this->markTestSkipped('No severe keywords configured.');
        }

        $service = new ModerationService();
        $result = $service->check("You are such a {$severe}");

        $this->assertTrue($result->flagged);
        $this->assertFalse($result->deliver);
    }

    public function test_contact_pattern_is_flagged_but_delivered(): void
    {
        $service = new ModerationService();
        $result = $service->check('Call me at 0244123456 or whatsapp me');

        $this->assertTrue($result->flagged);
        $this->assertTrue($result->deliver);
    }
}
