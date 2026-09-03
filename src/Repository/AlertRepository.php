<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Alert;
use App\Enum\AlertStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alert::class);
    }

    public function findOpenAlerts(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.status IN (:statuses)')
            ->setParameter('statuses', [AlertStatus::OPEN, AlertStatus::ACKNOWLEDGED])
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()->getResult();
    }

    public function findByFilters(?string $type, ?string $severity, ?string $status): array
    {
        $qb = $this->createQueryBuilder('a');
        if ($type) { $qb->andWhere('a.alertType = :type')->setParameter('type', $type); }
        if ($severity) { $qb->andWhere('a.severity = :severity')->setParameter('severity', $severity); }
        if ($status) { $qb->andWhere('a.status = :status')->setParameter('status', $status); }
        return $qb->orderBy('a.createdAt', 'DESC')->getQuery()->getResult();
    }

    public function countBySeverity(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.severity, COUNT(a.id) as count')
            ->groupBy('a.severity')
            ->getQuery()->getResult();
    }

    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }
}
