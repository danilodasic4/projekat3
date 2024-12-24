<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Car;
use Doctrine\ORM\QueryBuilder;

class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function findAppointmentByCarAndTime($car, $scheduledAt)
    {
        return $this->findOneBy([
            'car' => $car,
            'scheduledAt' => $scheduledAt
        ]);
    }
    public function findAppointmentsByCar(Car $car): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.car = :car')
            ->setParameter('car', $car)
            ->orderBy('a.scheduledAt', 'ASC')  
            ->getQuery()
            ->getResult();
    }
    public function findByUser( $user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
    public function createFilteredQueryBuilder(
        ?int $carId,
        ?int $userId,
        ?string $appointmentType,
        ?\DateTimeInterface $scheduledAt
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('a');
    
        if ($carId) {
            $queryBuilder->andWhere('a.car = :carId')
                ->setParameter('carId', $carId);
        }
    
        if ($userId) {
            $queryBuilder->andWhere('a.user = :userId')
                ->setParameter('userId', $userId);
        }
    
        if ($appointmentType) {
            $queryBuilder->andWhere('a.appointmentType = :appointmentType')
                ->setParameter('appointmentType', $appointmentType);
        }
    
        if ($scheduledAt) {
            // Strip time part for comparison
            $startOfDay = clone $scheduledAt;
            $startOfDay->setTime(0, 0); // Set the time to 00:00:00
    
            $endOfDay = clone $scheduledAt;
            $endOfDay->setTime(23, 59, 59); // Set the time to 23:59:59 TO be just that day
    
            $queryBuilder->andWhere('a.scheduledAt BETWEEN :startOfDay AND :endOfDay')
                ->setParameter('startOfDay', $startOfDay)
                ->setParameter('endOfDay', $endOfDay);
        }
    
    
        return $queryBuilder;
    }
}

