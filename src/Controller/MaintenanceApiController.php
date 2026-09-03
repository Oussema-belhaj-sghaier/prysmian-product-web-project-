<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MaintenanceLog;
use App\Enum\MaintenanceResultStatus;
use App\Enum\MaintenanceType;
use App\Repository\MaintenanceLogRepository;
use App\Repository\CableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/maintenances')]
class MaintenanceApiController extends AbstractController
{
    #[Route('', name: 'api_maintenances_list', methods: ['GET'])]
    public function list(MaintenanceLogRepository $repo): JsonResponse
    {
        $logs = $repo->findRecent(50);
        return $this->json([
            'data' => array_map(fn($m) => [
                'id' => $m->getId(),
                'cableReference' => $m->getCable()->getReferenceCode(),
                'factory' => $m->getCable()->getFactory(),
                'type' => $m->getMaintenanceType()->value,
                'technician' => $m->getTechnician()?->getFullName(),
                'startDate' => $m->getStartDate()->format('c'),
                'endDate' => $m->getEndDate()?->format('c'),
                'durationHours' => $m->getDurationHours(),
                'cost' => $m->getCost(),
                'resultStatus' => $m->getResultStatus()->value,
                'description' => $m->getDescription(),
            ], $logs),
        ]);
    }

    #[Route('', name: 'api_maintenances_create', methods: ['POST'])]
    public function create(Request $request, CableRepository $cableRepo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $cable = $cableRepo->find($data['cableId'] ?? '');
        if (!$cable) {
            return $this->json(['error' => 'Référence produit introuvable'], 404);
        }

        $log = new MaintenanceLog();
        $log->setCable($cable)
            ->setMaintenanceType(MaintenanceType::from($data['maintenanceType'] ?? 'EXTRUSION'))
            ->setDescription($data['description'] ?? '')
            ->setStartDate(new \DateTimeImmutable($data['startDate'] ?? 'now'))
            ->setEndDate(isset($data['endDate']) ? new \DateTimeImmutable($data['endDate']) : null)
            ->setCost($data['cost'] ?? 0.0)
            ->setNotes($data['notes'] ?? null)
            ->setResultStatus(MaintenanceResultStatus::from($data['resultStatus'] ?? 'PLANNED'));

        $em->persist($log);
        $em->flush();

        return $this->json(['id' => $log->getId(), 'message' => 'Maintenance enregistrée'], 201);
    }
}
