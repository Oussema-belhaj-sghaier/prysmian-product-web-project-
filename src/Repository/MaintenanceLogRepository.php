<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MaintenanceLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaintenanceLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaintenanceLog::class);
    }

    public function findByCable(string $cableId): array
    {
        return $this->createQueryBuilder('m')
            ->join('m.cable', 'c')
            ->where('c.id = :cableId')
            ->setParameter('cableId', $cableId)
            ->orderBy('m.startDate', 'DESC')
            ->getQuery()->getResult();
    }

    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    public function getMonthlyCost(\DateTimeInterface $start, \DateTimeInterface $end): float
    {
        $result = $this->createQueryBuilder('m')
            ->select('SUM(m.cost)')
            ->where('m.startDate >= :start AND m.startDate <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()->getSingleScalarResult();
        return (float) ($result ?? 0.0);
    }
}
