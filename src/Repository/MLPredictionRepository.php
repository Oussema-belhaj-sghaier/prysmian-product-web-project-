<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MLPrediction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MLPredictionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MLPrediction::class);
    }

    public function findUpcoming(int $days = 7): array
    {
        $from = new \DateTimeImmutable();
        $to = $from->modify("+{$days} days");
        return $this->createQueryBuilder('p')
            ->where('p.predictedDate >= :from AND p.predictedDate <= :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('p.predictedDate', 'ASC')
            ->getQuery()->getResult();
    }

    public function findLatestByCable(string $cableId): ?MLPrediction
    {
        return $this->createQueryBuilder('p')
            ->join('p.cable', 'c')
            ->where('c.id = :cableId')
            ->setParameter('cableId', $cableId)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }
}
