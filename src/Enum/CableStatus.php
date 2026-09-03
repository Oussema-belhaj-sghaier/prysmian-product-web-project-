<?php

declare(strict_types=1);

namespace App\Enum;

enum CableStatus: string
{
    case IN_STOCK = 'IN_STOCK';           // Disponible en stock
    case IN_PRODUCTION = 'IN_PRODUCTION'; // En cours de fabrication
    case DISCONTINUED = 'DISCONTINUED';   // Référence discontinuée
    case OUT_OF_STOCK = 'OUT_OF_STOCK';   // Rupture de stock
    case QC_HOLD = 'QC_HOLD';            // Bloqué contrôle qualité
}
