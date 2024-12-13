<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Car;


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
}

