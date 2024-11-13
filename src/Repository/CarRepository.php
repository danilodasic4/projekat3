<?php

namespace App\Repository;

use App\Entity\Car;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    // Custom query to fetch all cars
    public function findAllCars(): array
    {
        return $this->findBy([], ['brand' => 'ASC']); // Sort by brand
    }

    // Custom query to fetch a car by its ID
    public function findCarById(int $id): ?Car
    {
        return $this->find($id);
    }
}
