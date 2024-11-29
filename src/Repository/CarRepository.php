<?php
namespace App\Repository;

use App\Entity\Car;
use App\Entity\User;  // Importuj User entitet
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;

class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    // Custom query to fetch all cars with user (eager loading)
    public function findAllCarsWithUser(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')  // JOIN sa korisnikom
            ->addSelect('u')  // Dodaj korisnika u selektovane podatke
            ->orderBy('c.brand', 'ASC')  // Sortiraj po brendu
            ->getQuery()
            ->getResult();
    }

    // Custom query to fetch a car by its ID with user (eager loading)
    public function findCarByIdWithUser(int $id): ?Car
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')  // JOIN sa korisnikom
            ->addSelect('u')  // Dodaj korisnika u selektovane podatke
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Custom query to fetch cars with registration expiring by a certain date
    public function findByRegistrationExpiringUntil(DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.registrationDate <= :endDate')
            ->setParameter('endDate', $endDate)
            ->orderBy('c.registrationDate', 'ASC')  // Sortiraj po datumu isteka registracije
            ->getQuery()
            ->getResult();
    }
}
