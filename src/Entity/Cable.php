<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CableStatus;
use App\Enum\CableType;
use App\Repository\CableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Entité représentant un produit câble du catalogue Prysmian.
 * Chaque enregistrement est un type de câble fabriqué (pas une installation).
 */
#[ORM\Entity(repositoryClass: CableRepository::class)]
#[ORM\Table(name: 'cables')]
#[ORM\HasLifecycleCallbacks]
class Cable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true)]
    private string $id;

    /** Référence produit unique (ex: PRY-HT-240-3X) */
    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    private string $referenceCode;

    /** Désignation commerciale du câble */
    #[ORM\Column(type: Types::STRING, length: 200)]
    private string $designation;

    /** Famille de câble */
    #[ORM\Column(type: Types::STRING, enumType: CableType::class)]
    private CableType $cableType;

    /** Statut du produit dans le catalogue */
    #[ORM\Column(type: Types::STRING, enumType: CableStatus::class)]
    private CableStatus $status;

    /** Tension nominale en kV */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $nominalVoltage = null;

    /** Section du conducteur en mm² */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $conductorSection = null;

    /** Matériau conducteur (COPPER ou ALUMINUM) */
    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $conductorMaterial = null;

    /** Nombre de conducteurs */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $numberOfConductors = null;

    /** Type d'isolation (XLPE, PVC, EPR...) */
    #[ORM\Column(type: Types::STRING, length: 30, nullable: true)]
    private ?string $insulation = null;

    /** Normes applicables (IEC 60502, NF C 33-210...) */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $standards = null;

    /** Prix au mètre en euros */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $pricePerMeter = null;

    /** Stock disponible en mètres */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $stockMeters = null;

    /** Seuil de stock minimal en mètres (alerte en dessous) */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $stockAlertThreshold = null;

    /** Usine de fabrication (Bizerte, Sfax...) */
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $factory = null;

    /** Description technique */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /** Chemin fiche technique PDF ou image */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $dataSheetPath = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $imagePath = null;

    /** Métadonnées JSON supplémentaires */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    /** @var Collection<int, MaintenanceLog> Ordres de production liés */
    #[ORM\OneToMany(targetEntity: MaintenanceLog::class, mappedBy: 'cable', cascade: ['persist', 'remove'])]
    private Collection $maintenanceLogs;

    /** @var Collection<int, Alert> Alertes liées à ce produit */
    #[ORM\OneToMany(targetEntity: Alert::class, mappedBy: 'cable', cascade: ['persist', 'remove'])]
    private Collection $alerts;

    /** @var Collection<int, MLPrediction> Prédictions QC liées */
    #[ORM\OneToMany(targetEntity: MLPrediction::class, mappedBy: 'cable', cascade: ['persist', 'remove'])]
    private Collection $mlPredictions;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->status = CableStatus::IN_STOCK;
        $this->maintenanceLogs = new ArrayCollection();
        $this->alerts = new ArrayCollection();
        $this->mlPredictions = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getReferenceCode(): string { return $this->referenceCode; }
    public function setReferenceCode(string $referenceCode): self { $this->referenceCode = $referenceCode; return $this; }
    public function getDesignation(): string { return $this->designation; }
    public function setDesignation(string $designation): self { $this->designation = $designation; return $this; }
    public function getCableType(): CableType { return $this->cableType; }
    public function setCableType(CableType $cableType): self { $this->cableType = $cableType; return $this; }
    public function getStatus(): CableStatus { return $this->status; }
    public function setStatus(CableStatus $status): self { $this->status = $status; return $this; }
    public function getNominalVoltage(): ?float { return $this->nominalVoltage; }
    public function setNominalVoltage(?float $nominalVoltage): self { $this->nominalVoltage = $nominalVoltage; return $this; }
    public function getConductorSection(): ?float { return $this->conductorSection; }
    public function setConductorSection(?float $conductorSection): self { $this->conductorSection = $conductorSection; return $this; }
    public function getConductorMaterial(): ?string { return $this->conductorMaterial; }
    public function setConductorMaterial(?string $conductorMaterial): self { $this->conductorMaterial = $conductorMaterial; return $this; }
    public function getNumberOfConductors(): ?int { return $this->numberOfConductors; }
    public function setNumberOfConductors(?int $numberOfConductors): self { $this->numberOfConductors = $numberOfConductors; return $this; }
    public function getInsulation(): ?string { return $this->insulation; }
    public function setInsulation(?string $insulation): self { $this->insulation = $insulation; return $this; }
    public function getStandards(): ?string { return $this->standards; }
    public function setStandards(?string $standards): self { $this->standards = $standards; return $this; }
    public function getPricePerMeter(): ?float { return $this->pricePerMeter; }
    public function setPricePerMeter(?float $pricePerMeter): self { $this->pricePerMeter = $pricePerMeter; return $this; }
    public function getStockMeters(): ?float { return $this->stockMeters; }
    public function setStockMeters(?float $stockMeters): self { $this->stockMeters = $stockMeters; return $this; }
    public function getStockAlertThreshold(): ?float { return $this->stockAlertThreshold; }
    public function setStockAlertThreshold(?float $stockAlertThreshold): self { $this->stockAlertThreshold = $stockAlertThreshold; return $this; }
    public function getFactory(): ?string { return $this->factory; }
    public function setFactory(?string $factory): self { $this->factory = $factory; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function getDataSheetPath(): ?string { return $this->dataSheetPath; }
    public function setDataSheetPath(?string $dataSheetPath): self { $this->dataSheetPath = $dataSheetPath; return $this; }
    public function getImagePath(): ?string { return $this->imagePath; }
    public function setImagePath(?string $imagePath): self { $this->imagePath = $imagePath; return $this; }
    public function getMetadata(): ?array { return $this->metadata; }
    public function setMetadata(?array $metadata): self { $this->metadata = $metadata; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }

    /** @return Collection<int, MaintenanceLog> */
    public function getMaintenanceLogs(): Collection { return $this->maintenanceLogs; }
    public function addMaintenanceLog(MaintenanceLog $log): self { if (!$this->maintenanceLogs->contains($log)) { $this->maintenanceLogs->add($log); $log->setCable($this); } return $this; }

    /** @return Collection<int, Alert> */
    public function getAlerts(): Collection { return $this->alerts; }
    public function addAlert(Alert $alert): self { if (!$this->alerts->contains($alert)) { $this->alerts->add($alert); $alert->setCable($this); } return $this; }

    /** @return Collection<int, MLPrediction> */
    public function getMlPredictions(): Collection { return $this->mlPredictions; }
    public function addMlPrediction(MLPrediction $prediction): self { if (!$this->mlPredictions->contains($prediction)) { $this->mlPredictions->add($prediction); $prediction->setCable($this); } return $this; }

    /**
     * Retourne la désignation courte pour l'affichage.
     */
    public function getShortLabel(): string
    {
        return '[' . $this->referenceCode . '] ' . $this->designation;
    }

    /**
     * Indique si le stock est bas.
     */
    public function isLowStock(): bool
    {
        if ($this->stockMeters === null || $this->stockAlertThreshold === null) {
            return false;
        }
        return $this->stockMeters <= $this->stockAlertThreshold;
    }
}

