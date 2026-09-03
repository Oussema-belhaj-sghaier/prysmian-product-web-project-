<?php
declare(strict_types=1);
namespace App\Controller;
use App\Repository\AlertRepository;
use App\Repository\CableRepository;
use App\Repository\MaintenanceLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends AbstractController
{
    #[Route('/report/pdf', name: 'app_report_pdf', methods: ['GET'])]
    public function exportPdf(
        CableRepository $cableRepo,
        AlertRepository $alertRepo,
        MaintenanceLogRepository $maintRepo,
    ): Response {
        $cables = $cableRepo->findAll();
        $alerts = $alertRepo->findOpenAlerts();
        $maintenances = $maintRepo->findRecent(20);

        // Calculs stats pour rapport fabrication
        $totalStockValue = 0.0;
        $lowStockCount = 0;
        foreach ($cables as $c) {
            if ($c->getStockMeters() !== null && $c->getPricePerMeter() !== null) {
                $totalStockValue += $c->getStockMeters() * $c->getPricePerMeter();
            }
            if ($c->isLowStock()) $lowStockCount++;
        }

        $totalProductionCost = 0.0;
        $conformeOrders = 0;
        foreach ($maintenances as $m) {
            $totalProductionCost += $m->getCost();
            if ($m->getResultStatus()->value === 'DONE') $conformeOrders++;
        }
        $qcRate = count($maintenances) > 0 ? round(($conformeOrders / count($maintenances)) * 100, 1) : 100.0;

        return $this->render('report/pdf.html.twig', [
            'cables' => $cables,
            'alerts' => $alerts,
            'maintenances' => $maintenances,
            'generatedAt' => new \DateTimeImmutable(),
            'totalStockValue' => $totalStockValue,
            'lowStockCount' => $lowStockCount,
            'totalProductionCost' => $totalProductionCost,
            'qcRate' => $qcRate,
        ]);
    }
}
