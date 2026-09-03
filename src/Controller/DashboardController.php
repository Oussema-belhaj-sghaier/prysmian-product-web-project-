<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\AlertRepository;
use App\Repository\CableRepository;
use App\Repository\MaintenanceLogRepository;
use App\Repository\MLPredictionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'api_dashboard_stats', methods: ['GET'])]
    public function stats(
        CableRepository $cableRepo,
        AlertRepository $alertRepo,
        MaintenanceLogRepository $maintRepo,
        MLPredictionRepository $predRepo,
    ): JsonResponse {
        $statusCounts = $cableRepo->countByStatus();
        $total = array_sum(array_column($statusCounts, 'count'));
        $inStock = 0;
        $inProduction = 0;
        $qcHold = 0;
        foreach ($statusCounts as $sc) {
            $statusVal = $sc['status'] instanceof \BackedEnum ? $sc['status']->value : (string) $sc['status'];
            match ($statusVal) {
                'IN_STOCK' => $inStock = (int) $sc['count'],
                'IN_PRODUCTION' => $inProduction = (int) $sc['count'],
                'QC_HOLD' => $qcHold = (int) $sc['count'],
                default => null,
            };
        }

        $now = new \DateTimeImmutable();
        $startMonth = $now->modify('first day of this month');
        $monthlyCost = $maintRepo->getMonthlyCost($startMonth, $now);

        $recentAlerts = $alertRepo->findRecent(10);
        $predictions = $predRepo->findUpcoming(7);

        return $this->json([
            'stats' => [
                'totalCables' => $total,
                'inStock' => $inStock,
                'inProduction' => $inProduction,
                'qcHold' => $qcHold,
                'other' => $total - $inStock - $inProduction - $qcHold,
            ],
            'monthlyCost' => $monthlyCost,
            'recentAlerts' => array_map(fn($a) => [
                'id' => $a->getId(),
                'type' => $a->getAlertType()->value,
                'severity' => $a->getSeverity()->value,
                'factory' => $a->getCable()->getFactory(),
                'cableReference' => $a->getCable()->getReferenceCode(),
                'message' => $a->getMessage(),
                'status' => $a->getStatus()->value,
                'createdAt' => $a->getCreatedAt()->format('c'),
            ], $recentAlerts),
            'predictions' => array_map(fn($p) => [
                'cableReference' => $p->getCable()->getReferenceCode(),
                'factory' => $p->getCable()->getFactory(),
                'predictedDate' => $p->getPredictedDate()->format('Y-m-d'),
                'confidence' => $p->getConfidenceScore(),
                'urgency' => $p->getMaintenanceUrgency(),
                'reason' => $p->getReason(),
            ], $predictions),
            'uptime' => 99.2,
            'mttr' => 4.2,
        ]);
    }
}
