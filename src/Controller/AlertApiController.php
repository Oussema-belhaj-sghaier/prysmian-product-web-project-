<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Alert;
use App\Enum\UserRole;
use App\Enum\AlertStatus;
use App\Repository\AlertRepository;
use App\Repository\UserRepository;
use App\Service\PlantMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/alerts')]
class AlertApiController extends AbstractController
{
    #[Route('', name: 'api_alerts_list', methods: ['GET'])]
    public function list(Request $request, AlertRepository $repo): JsonResponse
    {
        $type = $request->query->get('type');
        $severity = $request->query->get('severity');
        $status = $request->query->get('status');

        $alerts = $repo->findByFilters($type, $severity, $status);

        return $this->json([
            'data' => array_map(fn($a) => [
                'id' => $a->getId(),
                'type' => $a->getAlertType()->value,
                'severity' => $a->getSeverity()->value,
                'factory' => $a->getCable()->getFactory(),
                'cableReference' => $a->getCable()->getReferenceCode(),
                'message' => $a->getMessage(),
                'status' => $a->getStatus()->value,
                'createdAt' => $a->getCreatedAt()->format('c'),
                'acknowledgedBy' => $a->getAcknowledgedBy()?->getFullName(),
            ], $alerts),
            'count' => count($alerts),
        ]);
    }

    #[Route('/{id}/acknowledge', name: 'api_alerts_acknowledge', methods: ['POST'])]
    public function acknowledge(Alert $alert, EntityManagerInterface $em): JsonResponse
    {
        $alert->setStatus(AlertStatus::ACKNOWLEDGED)
            ->setAcknowledgedBy($this->getUser())
            ->setAcknowledgedAt(new \DateTimeImmutable());
        $em->flush();
        return $this->json(['message' => 'Alerte reconnue']);
    }

    #[Route('/{id}/resolve', name: 'api_alerts_resolve', methods: ['POST'])]
    public function resolve(Alert $alert, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $alert->setStatus(AlertStatus::RESOLVED)
            ->setResolvedAt(new \DateTimeImmutable())
            ->setResolution($data['resolution'] ?? 'Résolu sans commentaire');
        $em->flush();
        return $this->json(['message' => 'Alerte résolue']);
    }

    #[Route('/{id}/notify', name: 'api_alerts_notify', methods: ['POST'])]
    public function notify(Alert $alert, UserRepository $userRepository, \Symfony\Component\DependencyInjection\ContainerInterface $container): JsonResponse
    {
        $recipients = array_map(
            static fn ($user): string => $user->getEmail(),
            array_merge($userRepository->findByRole(UserRole::ADMIN), $userRepository->findByRole(UserRole::SUPERVISOR)),
        );
        if (($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev') === 'dev') {
            return $this->json(['message' => 'Notification simulée en environnement local', 'status' => 'local_mode', 'recipients' => count($recipients)]);
        }

        $deliveryStatus = $container->get(PlantMailerService::class)->sendAlert($alert, $recipients);

        return $this->json(['message' => 'Notification traitée', 'status' => $deliveryStatus, 'recipients' => count($recipients)]);
    }
}
