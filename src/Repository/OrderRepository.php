<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findOrdersWithEndDate(): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.endDate IS NOT NULL')
            ->orderBy('o.endDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les commandes qui expirent aujourd'hui
     */
    public function findOrdersExpiringToday(): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $tomorrow = clone $today;
        $tomorrow->add(new \DateInterval('P1D'));

        return $this->createQueryBuilder('o')
            ->where('o.endDate >= :today')
            ->andWhere('o.endDate < :tomorrow')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les commandes expirées depuis plus de X jours
     */
    public function findOrdersExpiredSince(int $days): array
    {
        $cutoffDate = new \DateTime();
        $cutoffDate->setTime(0, 0, 0);
        $cutoffDate->sub(new \DateInterval('P' . $days . 'D'));

        return $this->createQueryBuilder('o')
            ->where('o.endDate < :cutoffDate')
            ->andWhere('o.endDate IS NOT NULL')
            ->setParameter('cutoffDate', $cutoffDate)
            ->orderBy('o.endDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les commandes déjà expirées
     */
    public function findExpiredOrders(): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        return $this->createQueryBuilder('o')
            ->where('o.endDate < :today')
            ->andWhere('o.endDate IS NOT NULL')
            ->setParameter('today', $today)
            ->orderBy('o.endDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
