<?php

declare(strict_types=1);

namespace App\Enum;

enum AlertType: string
{
    case LOW_STOCK = 'LOW_STOCK';             // Stock bas
    case QC_FAILURE = 'QC_FAILURE';           // Échec contrôle qualité
    case PRODUCTION_DELAY = 'PRODUCTION_DELAY'; // Retard production
    case EQUIPMENT_FAULT = 'EQUIPMENT_FAULT'; // Panne équipement
    case URGENT_ORDER = 'URGENT_ORDER';       // Commande urgente
}
