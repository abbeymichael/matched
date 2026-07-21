<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case PendingVerification = 'pending_verification';
    case UnderReview = 'under_review';
    case Suspended = 'suspended';
    case Banned = 'banned';
}
