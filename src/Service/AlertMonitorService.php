<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Alert;
use App\Enum\AlertSeverity;
use App\Enum\AlertStatus;
use App\Enum\AlertType;
use App\Enum\CableStatus;
use App\Repository\AlertRepository;
use App\Repository\CableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service de monitoring et génération d'alertes automatiques.
 */
class AlertMonitorService
{
    private array $thresholds = [
        'production_delay_days' => 2,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private CableRepository $cableRepository,
        private AlertRepository $alertRepository,
        private LoggerInterface $logger,
    ) {}

    /**
     * Analyse tous les câbles et génère des alertes si nécessaire.
     */
    public function scanAllCables(): void
    {
        $this->logger->info('Démarrage scan alertes');
        $cables = $this->cableRepository->findAll();
        $newAlerts = 0;

        foreach ($cables as $cable) {
            if ($cable->getStatus() === CableStatus::INACTIVE) {
                continue;
            }

            $alerts = $this->analyzeCable($cable);
            foreach ($alerts as $alert) {
                $this->em->persist($alert);
                $newAlerts++;
            }
        }

        $this->em->flush();
        $this->logger->info('Scan alertes terminé', ['newAlerts' => $newAlerts]);
    }

    /**
     * Analyse un câble et retourne les alertes à créer.
     * @return Alert[]
     */
    public function analyzeCable(\App\Entity\Cable $cable): array
    {
        $alerts = [];
        if ($cable->isLowStock()) {
            $alerts[] = $this->createAlert(
                $cable,
                AlertType::LOW_STOCK,
                AlertSeverity::HIGH,
                sprintf('Stock faible pour %s : %.0f m disponibles (seuil : %.0f m)', $cable->getReferenceCode(), $cable->getStockMeters() ?? 0, $cable->getStockAlertThreshold() ?? 0)
            );
        }

        if ($cable->getStatus() === CableStatus::QC_HOLD) {
            $alerts[] = $this->createAlert(
                $cable,
                AlertType::QC_FAILURE,
                AlertSeverity::HIGH,
                'Référence bloquée dans l’attente du contrôle qualité.'
            );
        }

        return $alerts;
    }

    private function createAlert(\App\Entity\Cable $cable, AlertType $type, AlertSeverity $severity, string $message): Alert
    {
        $alert = new Alert();
        $alert->setCable($cable)
            ->setAlertType($type)
            ->setSeverity($severity)
            ->setMessage($message)
            ->setStatus(AlertStatus::OPEN);
        return $alert;
    }

    public function updateThresholds(array $thresholds): void
    {
        $this->thresholds = array_merge($this->thresholds, $thresholds);
    }

    public function getThresholds(): array
    {
        return $this->thresholds;
    }
}
