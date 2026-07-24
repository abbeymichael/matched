<?php

namespace App\Services;

final class ModerationResult
{
    public function __construct(
        public readonly bool $flagged,
        public readonly bool $deliver,
        public readonly ?string $reason,
    ) {}
}
