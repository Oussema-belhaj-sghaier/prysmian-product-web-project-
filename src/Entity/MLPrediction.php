<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PredictionType;
use App\Repository\MLPredictionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MLPredictionRepository::class)]
#[ORM\Table(name: 'ml_predictions')]
class MLPrediction
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Cable::class, inversedBy: 'mlPredictions')]
    #[ORM\JoinColumn(nullable: false)]
    private Cable $cable;

    #[ORM\Column(type: Types::STRING, enumType: PredictionType::class)]
    private PredictionType $predictionType;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $predictedDate;

    #[ORM\Column(type: Types::FLOAT)]
    private float $confidenceScore;

    #[ORM\Column(type: Types::FLOAT)]
    private float $maintenanceUrgency;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $reason;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $modelVersion;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getCable(): Cable { return $this->cable; }
    public function setCable(Cable $cable): self { $this->cable = $cable; return $this; }
    public function getPredictionType(): PredictionType { return $this->predictionType; }
    public function setPredictionType(PredictionType $predictionType): self { $this->predictionType = $predictionType; return $this; }
    public function getPredictedDate(): \DateTimeInterface { return $this->predictedDate; }
    public function setPredictedDate(\DateTimeInterface $predictedDate): self { $this->predictedDate = $predictedDate; return $this; }
    public function getConfidenceScore(): float { return $this->confidenceScore; }
    public function setConfidenceScore(float $confidenceScore): self { $this->confidenceScore = $confidenceScore; return $this; }
    public function getMaintenanceUrgency(): float { return $this->maintenanceUrgency; }
    public function setMaintenanceUrgency(float $maintenanceUrgency): self { $this->maintenanceUrgency = $maintenanceUrgency; return $this; }
    public function getReason(): string { return $this->reason; }
    public function setReason(string $reason): self { $this->reason = $reason; return $this; }
    public function getModelVersion(): string { return $this->modelVersion; }
    public function setModelVersion(string $modelVersion): self { $this->modelVersion = $modelVersion; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
}
