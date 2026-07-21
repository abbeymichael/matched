<?php

namespace App\Enums;

enum ReportReason: string
{
    case Harassment = 'harassment';
    case Threats = 'threats';
    case FakeProfile = 'fake_profile';
    case ExplicitContent = 'explicit_content';
    case HateSpeech = 'hate_speech';
    case Underage = 'underage';
    case Other = 'other';

    public function defaultSeverity(): ReportSeverity
    {
        return match ($this) {
            self::Threats, self::HateSpeech, self::Underage, self::Harassment => ReportSeverity::ZeroTolerance,
            self::FakeProfile, self::ExplicitContent, self::Other => ReportSeverity::Standard,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Harassment => 'Harassment',
            self::Threats => 'Threats',
            self::FakeProfile => 'Fake profile',
            self::ExplicitContent => 'Explicit content',
            self::HateSpeech => 'Hate speech',
            self::Underage => 'Underage user',
            self::Other => 'Other',
        };
    }
}
