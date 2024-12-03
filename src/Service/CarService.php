<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Car;
use App\Repository\CarRepository;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface; 
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Security;
use DateTimeImmutable;

class CarService
{
    private readonly string $apiHost;

    public function __construct(
        private readonly CarRepository $carRepository,
        private readonly EntityManagerInterFace $entityManager,
        private readonly HttpClientInterface $httpClient,
        private readonly Security $security, 
        private readonly ValidatorInterface $validator,
        string $apiHost,
 )  {
 $this->apiHost = $apiHost;
    }
    public function getCarsForUser(User $user): array
    {
        $cars = $this->carRepository->findBy(['user' => $user]);

        if (empty($cars)) {
            return [];
        }

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

    public function getAllCarsForUser(int $userId): array
    {
        $url = $this->apiHost . '/api/users/' . $userId . '/cars';

        $response = $this->httpClient->request('GET', $url);

        $content = $response->getContent(false);

        $rawData = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $rawData;
    }

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
        // Find car by ID
        $car = $this->entityManager->getRepository(Car::class)->find($id);

        if (!$car) {
            // Return error response if car is not found
            return new Response('Car not found', Response::HTTP_NOT_FOUND);
        }

        // Remove the car
        $this->entityManager->remove($car);
        $this->entityManager->flush();

        // Return success response
        return new Response('Car deleted successfully', Response::HTTP_OK);
    }
    public function getCarsWithExpiringRegistration(): array
    {
        $currentDate = new DateTimeImmutable();
        $endOfThisMonth = $currentDate->modify('last day of this month')->setTime(23, 59, 59);

        return $this->carRepository->findByRegistrationExpiringUntil($endOfThisMonth);
    }

}