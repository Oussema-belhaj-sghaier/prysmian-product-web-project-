<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AuditLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Table(name: 'audit_logs')]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $action;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $entityType;

    #[ORM\Column(type: Types::STRING, length: 36, nullable: true)]
    private ?string $entityId = null;

    #[ORM\Column(name: 'old_values', type: Types::JSON, nullable: true)]
    private ?array $oldValues = null;

    #[ORM\Column(name: 'new_values', type: Types::JSON, nullable: true)]
    private ?array $newValues = null;

    #[ORM\Column(name: 'ip_address', type: Types::STRING, length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getAction(): string { return $this->action; }
    public function setAction(string $action): self { $this->action = $action; return $this; }
    public function getEntityType(): string { return $this->entityType; }
    public function setEntityType(string $entityType): self { $this->entityType = $entityType; return $this; }
    public function getEntityId(): ?string { return $this->entityId; }
    public function setEntityId(?string $entityId): self { $this->entityId = $entityId; return $this; }
    public function getOldValues(): ?array { return $this->oldValues; }
    public function setOldValues(?array $oldValues): self { $this->oldValues = $oldValues; return $this; }
    public function getNewValues(): ?array { return $this->newValues; }
    public function setNewValues(?array $newValues): self { $this->newValues = $newValues; return $this; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ipAddress): self { $this->ipAddress = $ipAddress; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
}
