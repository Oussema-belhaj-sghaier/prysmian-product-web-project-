<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\UserRole;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true)]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: Types::STRING)]
    private string $password;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $firstName;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $lastName;

    #[ORM\Column(type: Types::STRING, enumType: UserRole::class)]
    private UserRole $role;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $regionAssigned = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $profileImagePath = null;

    #[ORM\Column(type: Types::STRING, enumType: UserStatus::class)]
    private UserStatus $status;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $updatedAt;

    /** @var Collection<int, MaintenanceLog> */
    #[ORM\OneToMany(targetEntity: MaintenanceLog::class, mappedBy: 'technician')]
    private Collection $maintenanceLogs;

    /** @var Collection<int, Alert> */
    #[ORM\OneToMany(targetEntity: Alert::class, mappedBy: 'acknowledgedBy')]
    private Collection $acknowledgedAlerts;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->status = UserStatus::ACTIVE;
        $this->maintenanceLogs = new ArrayCollection();
        $this->acknowledgedAlerts = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function getUserIdentifier(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    public function getFirstName(): string { return $this->firstName; }
    public function setFirstName(string $firstName): self { $this->firstName = $firstName; return $this; }
    public function getLastName(): string { return $this->lastName; }
    public function setLastName(string $lastName): self { $this->lastName = $lastName; return $this; }
    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }
    public function getRole(): UserRole { return $this->role; }
    public function setRole(UserRole $role): self { $this->role = $role; return $this; }
    public function getRoles(): array { return ['ROLE_' . $this->role->value]; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }
    public function getRegionAssigned(): ?string { return $this->regionAssigned; }
    public function setRegionAssigned(?string $regionAssigned): self { $this->regionAssigned = $regionAssigned; return $this; }
    public function getProfileImagePath(): ?string { return $this->profileImagePath; }
    public function setProfileImagePath(?string $profileImagePath): self { $this->profileImagePath = $profileImagePath; return $this; }
    public function getStatus(): UserStatus { return $this->status; }
    public function setStatus(UserStatus $status): self { $this->status = $status; return $this; }
    public function getLastLogin(): ?\DateTimeInterface { return $this->lastLogin; }
    public function setLastLogin(?\DateTimeInterface $lastLogin): self { $this->lastLogin = $lastLogin; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
    public function eraseCredentials(): void {}

    /** @return Collection<int, MaintenanceLog> */
    public function getMaintenanceLogs(): Collection { return $this->maintenanceLogs; }
    /** @return Collection<int, Alert> */
    public function getAcknowledgedAlerts(): Collection { return $this->acknowledgedAlerts; }
}
