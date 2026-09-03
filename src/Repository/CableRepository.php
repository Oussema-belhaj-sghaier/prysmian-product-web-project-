<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Cable;
use App\Enum\CableStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cable::class);
    }

    public function findByFilters(?string $factory, ?string $type, ?string $status, ?string $search): array
    {
        $qb = $this->createQueryBuilder('c');
        if ($factory) { $qb->andWhere('c.factory = :factory')->setParameter('factory', $factory); }
        if ($type) { $qb->andWhere('c.cableType = :type')->setParameter('type', $type); }
        if ($status) { $qb->andWhere('c.status = :status')->setParameter('status', CableStatus::from($status)); }
        if ($search) {
            $qb->andWhere('c.referenceCode LIKE :search OR c.designation LIKE :search OR c.factory LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        return $qb->orderBy('c.createdAt', 'DESC')->getQuery()->getResult();
    }

    public function countByStatus(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.status, COUNT(c.id) as count')
            ->groupBy('c.status')
            ->getQuery()->getResult();
    }

    public function findByFactory(string $factory): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.factory = :factory')
            ->setParameter('factory', $factory)
            ->getQuery()->getResult();
    }
}
