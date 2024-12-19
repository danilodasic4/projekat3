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
     /**
     * Find all cars with registration expiring by a certain date.
     *
     * @param DateTimeImmutable $endDate
     * @return Car[]
     */
    public function findAllExpiringUntil(DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.registrationDate <= :endDate')
            ->setParameter('endDate', $endDate)
            ->innerJoin('c.user', 'u') // Eager load the user
            ->addSelect('u')
            ->orderBy('u.email', 'ASC')
            ->addOrderBy('c.registrationDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findDeletedByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.deleted_at IS NOT NULL') 
            ->setParameter('user', $user)
            ->orderBy('c.deleted_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findSoftDeletedCarsOlderThan(DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.deleted_at IS NOT NULL') 
            ->andWhere('c.deleted_at <= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();

    }
    // CarRepository.php
public function findAllExpiringRegistrationsInNextMonth(DateTimeImmutable $currentDate): array
{
    return $this->createQueryBuilder('c')
        ->where('c.registrationDate <= :endDate')
        ->andWhere('c.registrationDate >= :currentDate')
        ->setParameter('currentDate', $currentDate)
        ->setParameter('endDate', $currentDate->modify('+1 month'))
        ->orderBy('c.registrationDate', 'ASC')
        ->getQuery()
        ->getResult();
}

}

