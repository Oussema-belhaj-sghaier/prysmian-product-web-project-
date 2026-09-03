<?php

declare(strict_types=1);

namespace App\Enum;

enum CableType: string
{
    case HT = 'HT';       // Haute Tension (>36 kV)
    case MT = 'MT';       // Moyenne Tension (1-36 kV)
    case BT = 'BT';       // Basse Tension (<1 kV)
    case FIBER = 'FIBER'; // Fibre Optique
    case SUBMARINE = 'SUBMARINE'; // Sous-marin
    case SPECIAL = 'SPECIAL';     // Câbles spéciaux industriels
}
