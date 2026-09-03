<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\AlertType;
use App\Enum\AlertSeverity;
use App\Enum\AlertStatus;
use App\Repository\AlertRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AlertRepository::class)]
#[ORM\Table(name: 'alerts')]
#[ORM\HasLifecycleCallbacks]
class Alert
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Cable::class, inversedBy: 'alerts')]
    #[ORM\JoinColumn(nullable: false)]
    private Cable $cable;

    #[ORM\Column(type: Types::STRING, enumType: AlertType::class)]
    private AlertType $alertType;

    #[ORM\Column(type: Types::STRING, enumType: AlertSeverity::class)]
    private AlertSeverity $severity;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $message;

    #[ORM\Column(type: Types::STRING, enumType: AlertStatus::class)]
    private AlertStatus $status;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'acknowledgedAlerts')]
    #[ORM\JoinColumn(name: 'acknowledged_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $acknowledgedBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $acknowledgedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $resolvedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resolution = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->createdAt = new \DateTimeImmutable();
        $this->status = AlertStatus::OPEN;
    }

    public function getId(): string { return $this->id; }
    public function getCable(): Cable { return $this->cable; }
    public function setCable(Cable $cable): self { $this->cable = $cable; return $this; }
    public function getAlertType(): AlertType { return $this->alertType; }
    public function setAlertType(AlertType $alertType): self { $this->alertType = $alertType; return $this; }
    public function getSeverity(): AlertSeverity { return $this->severity; }
    public function setSeverity(AlertSeverity $severity): self { $this->severity = $severity; return $this; }
    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): self { $this->message = $message; return $this; }
    public function getStatus(): AlertStatus { return $this->status; }
    public function setStatus(AlertStatus $status): self { $this->status = $status; return $this; }
    public function getAcknowledgedBy(): ?User { return $this->acknowledgedBy; }
    public function setAcknowledgedBy(?User $acknowledgedBy): self { $this->acknowledgedBy = $acknowledgedBy; return $this; }
    public function getAcknowledgedAt(): ?\DateTimeInterface { return $this->acknowledgedAt; }
    public function setAcknowledgedAt(?\DateTimeInterface $acknowledgedAt): self { $this->acknowledgedAt = $acknowledgedAt; return $this; }
    public function getResolvedAt(): ?\DateTimeInterface { return $this->resolvedAt; }
    public function setResolvedAt(?\DateTimeInterface $resolvedAt): self { $this->resolvedAt = $resolvedAt; return $this; }
    public function getResolution(): ?string { return $this->resolution; }
    public function setResolution(?string $resolution): self { $this->resolution = $resolution; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
}
