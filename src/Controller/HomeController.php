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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
			if ($cable->isLowStock()) $lowStock++;
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
		if (!$user) return $this->redirectToRoute('app_login');

		$cables = $cableRepo->findBy([], ['createdAt' => 'DESC']);
		$alerts = $alertRepo->findBy([], ['createdAt' => 'DESC']);
		$maintenances = $maintRepo->findBy([], ['startDate' => 'DESC']);
		$predictions = $predRepo->findBy([], ['createdAt' => 'DESC']);
		$users = $userRepo->findBy([], ['createdAt' => 'DESC']);

		$productData = array_map(static fn (Cable $cable): array => [
			'id' => $cable->getId(), 'reference' => $cable->getReferenceCode(),
			'designation' => $cable->getDesignation(), 'type' => $cable->getCableType()->value,
			'status' => $cable->getStatus()->value, 'stock' => $cable->getStockMeters(),
			'factory' => $cable->getFactory(), 'image' => $cable->getImagePath(),
			'description' => $cable->getDescription(), 'section' => $cable->getConductorSection(),
			'insulation' => $cable->getInsulation(),
		], $cables);
		$userData = array_map(static fn (User $member): array => [
			'id' => $member->getId(), 'firstName' => $member->getFirstName(),
			'lastName' => $member->getLastName(), 'email' => $member->getEmail(),
			'role' => $member->getRole()->value, 'status' => $member->getStatus()->value,
			'phone' => $member->getPhone(), 'region' => $member->getRegionAssigned(),
		], $users);

		$stats = [
			'total' => count($cables), 'inStock' => 0, 'inProduction' => 0,
			'discontinued' => 0, 'outOfStock' => 0, 'qcHold' => 0, 'lowStock' => 0,
			'totalStockMeters' => 0.0, 'totalStockValue' => 0.0, 'openAlerts' => 0,
			'criticalAlerts' => 0, 'ordersInProgress' => 0, 'ordersDone' => 0,
			'ordersRejected' => 0, 'ordersPlanned' => 0, 'totalProductionCost' => 0.0,
			'qcRate' => 100.0, 'byFamily' => ['HT' => 0, 'MT' => 0, 'BT' => 0, 'FIBER' => 0, 'SUBMARINE' => 0, 'SPECIAL' => 0],
		];
		foreach ($cables as $cable) {
			$status = $cable->getStatus()->value;
			$statusKey = match ($status) {
				'IN_STOCK' => 'inStock', 'IN_PRODUCTION' => 'inProduction',
				'DISCONTINUED' => 'discontinued', 'OUT_OF_STOCK' => 'outOfStock',
				'QC_HOLD' => 'qcHold', default => null,
			};
			if ($statusKey) $stats[$statusKey]++;
			if ($cable->isLowStock()) $stats['lowStock']++;
			$stats['totalStockMeters'] += $cable->getStockMeters() ?? 0.0;
			if ($cable->getStockMeters() !== null && $cable->getPricePerMeter() !== null) $stats['totalStockValue'] += $cable->getStockMeters() * $cable->getPricePerMeter();
			$family = $cable->getCableType()->value;
			if (isset($stats['byFamily'][$family])) $stats['byFamily'][$family]++;
		}
		foreach ($alerts as $alert) {
			$alertStatus = $alert->getStatus()->value;
			if (in_array($alertStatus, ['OPEN', 'ACKNOWLEDGED'], true)) $stats['openAlerts']++;
			if ($alert->getSeverity()->value === 'CRITICAL' && $alertStatus !== 'RESOLVED') $stats['criticalAlerts']++;
		}
		foreach ($maintenances as $maintenance) {
			$status = $maintenance->getResultStatus()->value;
			if ($status === 'IN_PROGRESS') $stats['ordersInProgress']++;
			if ($status === 'DONE') $stats['ordersDone']++;
			if ($status === 'REJECTED') $stats['ordersRejected']++;
			if ($status === 'PLANNED') $stats['ordersPlanned']++;
			$stats['totalProductionCost'] += $maintenance->getCost();
		}
		if (($stats['ordersDone'] + $stats['ordersRejected']) > 0) {
			$stats['qcRate'] = round(($stats['ordersDone'] / ($stats['ordersDone'] + $stats['ordersRejected'])) * 100, 1);
		}

		return $this->render('home/dashboard.html.twig', compact('user', 'cables', 'alerts', 'maintenances', 'predictions', 'stats') + [
			'allUsers' => $users, 'productData' => $productData, 'userData' => $userData,
		]);
	}
}
