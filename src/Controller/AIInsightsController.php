<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\CableStatus;
use App\Repository\CableRepository;
use App\Repository\MLPredictionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ai')]
class AIInsightsController extends AbstractController
{
    #[Route('/insights', name: 'api_ai_insights', methods: ['GET'])]
    public function insights(CableRepository $cableRepository, MLPredictionRepository $predictionRepository): JsonResponse
    {
        $cables = $cableRepository->findAll();
        $lowStock = array_values(array_filter($cables, static fn ($cable): bool => $cable->isLowStock()));
        $qcHold = array_values(array_filter($cables, static fn ($cable): bool => $cable->getStatus() === CableStatus::QC_HOLD));
        $inProduction = array_values(array_filter($cables, static fn ($cable): bool => $cable->getStatus() === CableStatus::IN_PRODUCTION));
        $predictions = $predictionRepository->findUpcoming(30);

        $recommendations = [];
        if ($lowStock !== []) {
            $recommendations[] = sprintf('Lancer un réapprovisionnement pour %d référence(s) sous le seuil.', count($lowStock));
        }
        if ($qcHold !== []) {
            $recommendations[] = sprintf('Prioriser le contrôle qualité de %d référence(s) bloquée(s).', count($qcHold));
        }
        if ($inProduction !== []) {
            $recommendations[] = sprintf('Surveiller les %d référence(s) actuellement en fabrication.', count($inProduction));
        }
        if ($recommendations === []) {
            $recommendations[] = 'Aucune action prioritaire détectée sur le catalogue.';
        }

        return $this->json([
            'generatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'engine' => 'Prysmian Plant Intelligence 1.0',
            'signals' => ['lowStock' => count($lowStock), 'qcHold' => count($qcHold), 'inProduction' => count($inProduction), 'predictions30Days' => count($predictions)],
            'recommendations' => $recommendations,
        ]);
    }
}