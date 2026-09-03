<?php
declare(strict_types=1);
namespace App\Controller;
use App\Entity\Cable;
use App\Entity\User;
use App\Repository\AlertRepository;
use App\Repository\CableRepository;
use App\Repository\MaintenanceLogRepository;
use App\Repository\MLPredictionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/dashboard/live', name: 'app_dashboard_live', methods: ['GET'])]
    public function live(CableRepository $cableRepo, MaintenanceLogRepository $maintRepo): JsonResponse
    {
        $cables = $cableRepo->findAll();
        $orders = $maintRepo->findRecent(100);
        $statusCounts = [];
        $stockMeters = 0.0;
        $lowStock = 0;

        foreach ($cables as $cable) {
            $status = $cable->getStatus()->value;
            $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
            $stockMeters += $cable->getStockMeters() ?? 0.0;
            if ($cable->isLowStock()) {
                $lowStock++;
            }
        }

        $activeOrders = count(array_filter($orders, static fn ($order): bool => in_array($order->getResultStatus()->value, ['PLANNED', 'IN_PROGRESS', 'QC_CHECK'], true)));

        return $this->json([
            'updatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'products' => count($cables),
            'stockMeters' => round($stockMeters),
            'lowStock' => $lowStock,
            'activeOrders' => $activeOrders,
            'statusCounts' => $statusCounts,
        ]);
    }

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(
        CableRepository $cableRepo,
        AlertRepository $alertRepo,
        MaintenanceLogRepository $maintRepo,
        MLPredictionRepository $predRepo,
        UserRepository $userRepo,
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cables = $cableRepo->findBy([], ['createdAt' => 'DESC']);
        $alerts = $alertRepo->findBy([], ['createdAt' => 'DESC']);
        $maintenances = $maintRepo->findBy([], ['startDate' => 'DESC']);
        $predictions = $predRepo->findBy([], ['createdAt' => 'DESC']);
        $users = $userRepo->findBy([], ['createdAt' => 'DESC']);
        $productData = array_map(static fn (Cable $cable): array => [
            'id' => $cable->getId(),
            'reference' => $cable->getReferenceCode(),
            'designation' => $cable->getDesignation(),
            'type' => $cable->getCableType()->value,
            'status' => $cable->getStatus()->value,
            'stock' => $cable->getStockMeters(),
            'factory' => $cable->getFactory(),
            'image' => $cable->getImagePath(),
            'description' => $cable->getDescription(),
            'section' => $cable->getConductorSection(),
            'insulation' => $cable->getInsulation(),
        ], $cables);
        $userData = array_map(static fn (User $member): array => [
            'id' => $member->getId(),
            'firstName' => $member->getFirstName(),
            'lastName' => $member->getLastName(),
            'email' => $member->getEmail(),
            'role' => $member->getRole()->value,
            'status' => $member->getStatus()->value,
            'phone' => $member->getPhone(),
            'region' => $member->getRegionAssigned(),
        ], $users);

        // Stats catalogue produits
        $totalProducts = count($cables);
        $inStockProducts = 0;
        $inProductionProducts = 0;
        $discontinuedProducts = 0;
        $outOfStockProducts = 0;
        $qcHoldProducts = 0;
        $lowStockProducts = 0;
        $totalStockValue = 0.0;
        $totalStockMeters = 0.0;

        // Stats par famille
        $byFamily = ['HT' => 0, 'MT' => 0, 'BT' => 0, 'FIBER' => 0, 'SUBMARINE' => 0, 'SPECIAL' => 0];

        foreach ($cables as $c) {
            $st = $c->getStatus()->value;
            switch ($st) {
                case 'IN_STOCK': $inStockProducts++; break;
                case 'IN_PRODUCTION': $inProductionProducts++; break;
                case 'DISCONTINUED': $discontinuedProducts++; break;
                case 'OUT_OF_STOCK': $outOfStockProducts++; break;
                case 'QC_HOLD': $qcHoldProducts++; break;
            }
            if ($c->isLowStock()) {
                $lowStockProducts++;
            }
            if ($c->getStockMeters() !== null) {
                $totalStockMeters += $c->getStockMeters();
            }
            if ($c->getStockMeters() !== null && $c->getPricePerMeter() !== null) {
                $totalStockValue += $c->getStockMeters() * $c->getPricePerMeter();
            }
            $family = $c->getCableType()->value;
            if (isset($byFamily[$family])) {
                $byFamily[$family]++;
            }
        }

        // Stats alertes usine
        $openAlertsCount = 0;
        $criticalAlertsCount = 0;
        foreach ($alerts as $a) {
            $ast = $a->getStatus()->value;
            if ($ast === 'OPEN' || $ast === 'ACKNOWLEDGED') {
                $openAlertsCount++;
            }
            $sev = $a->getSeverity()->value;
            if ($sev === 'CRITICAL' && $ast !== 'RESOLVED') {
                $criticalAlertsCount++;
            }
        }

        // Stats ordres de production
        $ordersInProgress = 0;
        $ordersDone = 0;
        $ordersRejected = 0;
        $ordersPlanned = 0;
        $totalProductionCost = 0.0;
        foreach ($maintenances as $m) {
            $st = $m->getResultStatus()->value;
            switch ($st) {
                case 'IN_PROGRESS': $ordersInProgress++; break;
                case 'DONE': $ordersDone++; break;
                case 'REJECTED': $ordersRejected++; break;
                case 'PLANNED': $ordersPlanned++; break;
            }
            $totalProductionCost += $m->getCost();
        }

        // Taux de conformité QC
        $qcRate = ($ordersDone + $ordersRejected) > 0
            ? round(($ordersDone / ($ordersDone + $ordersRejected)) * 100, 1)
            : 100.0;

        return $this->render('home/dashboard.html.twig', [
            'user' => $user,
            'cables' => $cables,
            'alerts' => $alerts,
            'maintenances' => $maintenances,
            'predictions' => $predictions,
            'allUsers' => $users,
            'productData' => $productData,
            'userData' => $userData,
            'stats' => [
                'total' => $totalProducts,
                'inStock' => $inStockProducts,
                'inProduction' => $inProductionProducts,
                'discontinued' => $discontinuedProducts,
                'outOfStock' => $outOfStockProducts,
                'qcHold' => $qcHoldProducts,
                'lowStock' => $lowStockProducts,
                'totalStockMeters' => $totalStockMeters,
                'totalStockValue' => $totalStockValue,
                'openAlerts' => $openAlertsCount,
                'criticalAlerts' => $criticalAlertsCount,
                'ordersInProgress' => $ordersInProgress,
                'ordersDone' => $ordersDone,
                'ordersRejected' => $ordersRejected,
                'ordersPlanned' => $ordersPlanned,
                'totalProductionCost' => $totalProductionCost,
                'qcRate' => $qcRate,
                'byFamily' => $byFamily,
            ]
        ]);
    }
}
