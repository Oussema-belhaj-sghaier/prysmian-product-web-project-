<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\AlertRepository;
use App\Repository\CableRepository;
use App\Repository\MaintenanceLogRepository;
use App\Repository\MLPredictionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WorkspaceController extends AbstractController
{
    public function __construct(
        private CableRepository $cableRepository,
        private AlertRepository $alertRepository,
        private MaintenanceLogRepository $maintenanceRepository,
        private MLPredictionRepository $predictionRepository,
        private UserRepository $userRepository,
    ) {
    }

    #[Route('/catalogue', name: 'app_catalogue', methods: ['GET'])]
    public function catalogue(): Response
    {
        return $this->renderModule('catalogue', 'Catalogue produits');
    }

    #[Route('/production', name: 'app_production', methods: ['GET'])]
    public function production(): Response
    {
        return $this->renderModule('production', 'Ordres de fabrication');
    }

    #[Route('/qualite', name: 'app_quality', methods: ['GET'])]
    public function quality(): Response
    {
        return $this->renderModule('quality', 'Qualité et traçabilité', 'all');
    }

    #[Route('/alertes', name: 'app_alerts', methods: ['GET'])]
    public function alerts(): Response
    {
        return $this->renderModule('quality', 'Alertes qualité', 'alerts');
    }

    #[Route('/analyses', name: 'app_analyses', methods: ['GET'])]
    public function analyses(): Response
    {
        return $this->renderModule('quality', 'Prédictions IA', 'analysis');
    }

    #[Route('/utilisateurs', name: 'app_users', methods: ['GET'])]
    public function users(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->render('security/access_denied.html.twig', [
                'activePage' => 'users',
                'requestedSpace' => 'Gestion des utilisateurs',
            ], new \Symfony\Component\HttpFoundation\Response('', 403));
        }

        return $this->renderModule('users', 'Équipe de production');
    }

    private function renderModule(string $module, string $title, string $qualityView = 'all'): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('home/module.html.twig', [
            'module' => $module,
            'moduleTitle' => $title,
            'activePage' => $qualityView === 'alerts' ? 'alerts' : ($qualityView === 'analysis' ? 'analysis' : $module),
            'qualityView' => $qualityView,
            'cables' => $this->cableRepository->findBy([], ['createdAt' => 'DESC']),
            'alerts' => $this->alertRepository->findBy([], ['createdAt' => 'DESC']),
            'maintenances' => $this->maintenanceRepository->findBy([], ['startDate' => 'DESC']),
            'predictions' => $this->predictionRepository->findBy([], ['createdAt' => 'DESC']),
            'users' => $this->userRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }
}
