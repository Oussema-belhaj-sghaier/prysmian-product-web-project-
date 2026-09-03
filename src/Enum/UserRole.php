<?php

declare(strict_types=1);

namespace App\Enum;

enum UserRole: string
{
    case ADMIN = 'ADMIN';           // Administrateur
    case SUPERVISOR = 'SUPERVISOR'; // Responsable production
    case TECHNICIAN = 'TECHNICIAN'; // Opérateur / Technicien
    case COMMERCIAL = 'COMMERCIAL'; // Commercial / Ventes
}
