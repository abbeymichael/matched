<?php

namespace App\Enums;

enum ModerationAction: string
{
    case Dismissed = 'dismissed';
    case Warned = 'warned';
    case Suspended = 'suspended';
    case Banned = 'banned';
}
