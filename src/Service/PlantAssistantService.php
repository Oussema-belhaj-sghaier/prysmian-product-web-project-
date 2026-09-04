<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\CableStatus;
use App\Enum\CableType;
use App\Repository\CableRepository;
use App\Repository\MaintenanceLogRepository;

class PlantAssistantService
{
    public function __construct(
        private CableRepository $cableRepository,
        private MaintenanceLogRepository $maintenanceRepository,
    ) {
    }

    public function answer(string $question): string
    {
        $question = mb_strtolower(trim($question));
        $cables = $this->cableRepository->findAll();
        $lowStock = count(array_filter($cables, static fn ($cable): bool => $cable->isLowStock()));
        $production = count(array_filter($cables, static fn ($cable): bool => $cable->getStatus() === CableStatus::IN_PRODUCTION));
        $orders = count($this->maintenanceRepository->findRecent(100));

        if (str_contains($question, 'fibre') || str_contains($question, 'fibre optique') || str_contains($question, 'débit') || str_contains($question, 'debit')) {
            $fiberCables = array_values(array_filter($cables, static fn ($cable): bool => $cable->getCableType() === CableType::FIBER));
            if ($fiberCables === []) {
                return 'Aucun produit fibre optique n’est enregistré dans le catalogue. Ajoutez une référence fibre pour consulter ses caractéristiques.';
            }

            $labels = array_map(static fn ($cable): string => sprintf('%s (%s)', $cable->getReferenceCode(), $cable->getDesignation()), $fiberCables);
            return sprintf('%d produit(s) fibre optique sont disponibles dans le catalogue : %s. Le débit exact n’est pas encore enregistré dans les caractéristiques produit.', count($fiberCables), implode(', ', $labels));
        }

        if (str_contains($question, 'stock')) {
            return sprintf('Le stock compte %d référence(s) sous le seuil. Je recommande de vérifier les besoins de réapprovisionnement avant le prochain cycle.', $lowStock);
        }
        if (str_contains($question, 'production') || str_contains($question, 'ordre')) {
            return sprintf('%d référence(s) sont actuellement en fabrication et %d ordre(s) sont enregistrés. Priorisez les ordres en contrôle qualité.', $production, $orders);
        }
        if (str_contains($question, 'qualité') || str_contains($question, 'qualite')) {
            $qc = count(array_filter($cables, static fn ($cable): bool => $cable->getStatus() === CableStatus::QC_HOLD));
            return sprintf('%d référence(s) sont bloquées en contrôle qualité. Consultez la page Qualité pour traiter les écarts.', $qc);
        }

        return sprintf('Je peux vous aider sur le stock (%d alerte(s)), la production (%d produit(s)), la qualité et les câbles fibre optique. Posez une question précise.', $lowStock, $production);
    }
}
