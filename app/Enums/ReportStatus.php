<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case ReviewedDismissed = 'reviewed_dismissed';
    case ReviewedActioned = 'reviewed_actioned';
}
