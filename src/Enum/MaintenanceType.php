<?php

declare(strict_types=1);

namespace App\Enum;

enum MaintenanceType: string
{
    case EXTRUSION = 'EXTRUSION';         // Ligne d'extrusion
    case STRANDING = 'STRANDING';         // Toronneuse
    case ARMORING = 'ARMORING';           // Armurage
    case TESTING = 'TESTING';             // Tests électriques
    case PACKAGING = 'PACKAGING';         // Emballage / bobinage
}
