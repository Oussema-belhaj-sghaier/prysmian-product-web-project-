<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\MaintenanceType;
use App\Enum\MaintenanceResultStatus;
use App\Repository\MaintenanceLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Ordre de production : commande de fabrication d'un câble en usine.
 */
#[ORM\Entity(repositoryClass: MaintenanceLogRepository::class)]
#[ORM\Table(name: 'maintenance_logs')]
#[ORM\HasLifecycleCallbacks]
class MaintenanceLog
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Cable::class, inversedBy: 'maintenanceLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private Cable $cable;

    /** Opérateur responsable de l'ordre de production */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'maintenanceLogs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $technician = null;

    /** Type de ligne de production utilisée */
    #[ORM\Column(type: Types::STRING, enumType: MaintenanceType::class)]
    private MaintenanceType $maintenanceType;

    /** Numéro d'ordre de fabrication (ex: OF-2024-0123) */
    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $orderNumber = null;

    /** Longueur à produire en mètres */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $targetLengthMeters = null;

    /** Longueur produite effectivement en mètres */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $producedLengthMeters = null;

    /** Description / instructions de production */
    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    /** Coût de production en euros */
    #[ORM\Column(type: Types::FLOAT)]
    private float $cost;

    /** Notes et observations qualité */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    /** Statut de l'ordre de production */
    #[ORM\Column(type: Types::STRING, enumType: MaintenanceResultStatus::class)]
    private MaintenanceResultStatus $resultStatus;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->createdAt = new \DateTimeImmutable();
        $this->resultStatus = MaintenanceResultStatus::PLANNED;
    }

    public function getDurationHours(): ?float
    {
        if ($this->endDate === null || $this->startDate === null) {
            return null;
        }
        return $this->endDate->diff($this->startDate)->h + ($this->endDate->diff($this->startDate)->days * 24);
    }

    /** Rendement de production (%) */
    public function getYieldPercent(): ?float
    {
        if ($this->targetLengthMeters === null || $this->targetLengthMeters == 0 || $this->producedLengthMeters === null) {
            return null;
        }
        return round(($this->producedLengthMeters / $this->targetLengthMeters) * 100, 1);
    }

    public function getId(): string { return $this->id; }
    public function getCable(): Cable { return $this->cable; }
    public function setCable(Cable $cable): self { $this->cable = $cable; return $this; }
    public function getTechnician(): ?User { return $this->technician; }
    public function setTechnician(?User $technician): self { $this->technician = $technician; return $this; }
    public function getMaintenanceType(): MaintenanceType { return $this->maintenanceType; }
    public function setMaintenanceType(MaintenanceType $maintenanceType): self { $this->maintenanceType = $maintenanceType; return $this; }
    public function getOrderNumber(): ?string { return $this->orderNumber; }
    public function setOrderNumber(?string $orderNumber): self { $this->orderNumber = $orderNumber; return $this; }
    public function getTargetLengthMeters(): ?float { return $this->targetLengthMeters; }
    public function setTargetLengthMeters(?float $targetLengthMeters): self { $this->targetLengthMeters = $targetLengthMeters; return $this; }
    public function getProducedLengthMeters(): ?float { return $this->producedLengthMeters; }
    public function setProducedLengthMeters(?float $producedLengthMeters): self { $this->producedLengthMeters = $producedLengthMeters; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }
    public function getStartDate(): \DateTimeInterface { return $this->startDate; }
    public function setStartDate(\DateTimeInterface $startDate): self { $this->startDate = $startDate; return $this; }
    public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }
    public function setEndDate(?\DateTimeInterface $endDate): self { $this->endDate = $endDate; return $this; }
    public function getCost(): float { return $this->cost; }
    public function setCost(float $cost): self { $this->cost = $cost; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): self { $this->notes = $notes; return $this; }
    public function getResultStatus(): MaintenanceResultStatus { return $this->resultStatus; }
    public function setResultStatus(MaintenanceResultStatus $resultStatus): self { $this->resultStatus = $resultStatus; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
}
