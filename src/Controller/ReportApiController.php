<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\AlertRepository;
use App\Repository\CableRepository;
use App\Repository\MaintenanceLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/reports')]
class ReportApiController extends AbstractController
{
    #[Route('/monthly', name: 'api_reports_monthly', methods: ['GET'])]
    public function monthly(
        CableRepository $cableRepo,
        MaintenanceLogRepository $maintRepo,
        AlertRepository $alertRepo,
    ): JsonResponse {
        $now = new \DateTimeImmutable();
        $startMonth = $now->modify('first day of this month');

        $monthlyCost = $maintRepo->getMonthlyCost($startMonth, $now);
        $severityCounts = $alertRepo->countBySeverity();

        return $this->json([
            'month' => $now->format('F Y'),
            'cablesAdded' => rand(5, 20), // À remplacer par vraie requête
            'maintenancesCount' => count($maintRepo->findRecent(1000)),
            'totalCost' => $monthlyCost,
            'alertsBySeverity' => array_column($severityCounts, 'count', 'severity'),
            'uptime' => 99.2,
            'mttr' => 4.2,
        ]);
    }

    #[Route('/costs', name: 'api_reports_costs', methods: ['GET'])]
    public function costs(MaintenanceLogRepository $repo): JsonResponse
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = (new \DateTimeImmutable())->modify("first day of -{$i} months");
            $end = $start->modify('last day of this month');
            $months[] = [
                'month' => $start->format('M Y'),
                'cost' => $repo->getMonthlyCost($start, $end),
            ];
        }
        return $this->json(['data' => $months]);
    }
}
