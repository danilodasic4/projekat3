<?php
namespace App\Repository;

use App\Entity\Car;
use App\Entity\User;
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
            ->leftJoin('c.user', 'u')
            ->addSelect('u') 
            ->orderBy('c.brand', 'ASC') 
            ->getQuery()
            ->getResult();
    }

    // Custom query to fetch a car by its ID with user (eager loading)
    public function findCarByIdWithUser(int $id): ?Car
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')  
            ->addSelect('u')  
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Custom query to fetch cars with registration expiring by a certain date
    public function findByRegistrationExpiringUntil(User $user, DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.registrationDate <= :endDate')
            ->setParameter('user',$user)
            ->setParameter('endDate', $endDate)
            ->orderBy('c.registrationDate', 'ASC')  
            ->getQuery()
            ->getResult();
    }
}
