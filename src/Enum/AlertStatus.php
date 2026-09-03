<?php

declare(strict_types=1);

namespace App\Enum;

enum AlertStatus: string
{
    case OPEN = 'OPEN';
    case ACKNOWLEDGED = 'ACKNOWLEDGED';
    case RESOLVED = 'RESOLVED';
}
