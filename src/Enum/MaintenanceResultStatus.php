<?php

declare(strict_types=1);

namespace App\Enum;

enum MaintenanceResultStatus: string
{
    case PLANNED = 'PLANNED';         // Planifié
    case IN_PROGRESS = 'IN_PROGRESS'; // En cours
    case QC_CHECK = 'QC_CHECK';       // En contrôle qualité
    case DONE = 'DONE';               // Terminé - Conforme
    case REJECTED = 'REJECTED';       // Rejeté - Non conforme
}
