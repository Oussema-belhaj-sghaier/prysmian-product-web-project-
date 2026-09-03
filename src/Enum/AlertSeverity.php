<?php

declare(strict_types=1);

namespace App\Enum;

enum AlertSeverity: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';
    case CRITICAL = 'CRITICAL';
}
