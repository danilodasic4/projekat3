<?php

namespace App\Service;

use App\Entity\Car;
use App\Repository\CarRepository;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface; 
use DateTimeImmutable;

class CarService
{
    public function __construct(
        private readonly CarRepository $carRepository,
        private readonly EntityManagerInterFace $entityManager,
        private readonly ValidatorInterface $validator)
    {}

    // Get all cars
    public function getAllCars(): array
    {
        $cars = $this->carRepository->findAll();

        return array_map(function (Car $car) {
            return [
                'id' => $car->getId(),
                'brand' => $car->getBrand(),
                'model' => $car->getModel(),
                'year' => $car->getYear(),
                'color' => $car->getColor(),
            ];
        }, $cars);
    }

    // Get car by ID
    public function getCarById(int $id): ?Car
    {
        return $this->carRepository->find($id);
    }
    public function createNewCar(Car $car): Response
    {
        $errors = $this->validator->validate($car);
        if (count($errors) > 0) {
            return new Response('Invalid input data: ' . (string) $errors, Response::HTTP_BAD_REQUEST);
        }

        $car->setCreatedAt(new \DateTimeImmutable());
        
        try {
            $this->entityManager->persist($car);
            $this->entityManager->flush();

            return new Response('Car created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
    public function updateCar(Car $car): Response
    {
        $errors = $this->validator->validate($car);
        if (count($errors) > 0) {
            return new Response('Invalid input data: ' . (string) $errors, Response::HTTP_BAD_REQUEST);
        }

        $car->setUpdatedAt(new \DateTimeImmutable());

        try {
            $this->entityManager->flush();

            return new Response('Car updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
    public function deleteCarById(int $id): Response
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);

        if (!$car) {
            return new Response('Car not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($car);
            $this->entityManager->flush();

            return new Response('Car deleted successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getCarsWithExpiringRegistration(): array
    {
        $currentDate = new DateTimeImmutable();
        $endOfThisMonth = $currentDate->modify('last day of this month')->setTime(23, 59, 59);

        return $this->carRepository->findByRegistrationExpiringUntil($endOfThisMonth);
    }

}