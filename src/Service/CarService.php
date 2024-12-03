<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Car;
use App\Repository\CarRepository;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
        private readonly LoggerInterface $logger, 
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

        $this->logger->info('Fetching all cars'); 

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
        try {
            $this->logger->info('Fetching car with ID', ['car_id' => $id]); 

            $car = $this->carRepository->find($id);

            if (!$car) {
                $this->logger->error('Car not found', ['car_id' => $id]); 
            }

            return $car;
        } catch (\Exception $e) {
           
            $this->logger->error('Error fetching car by ID', [
                'car_id' => $id,
                'error_message' => $e->getMessage(),
            ]);
            throw $e; 
        }
    }

    // Create new car
    public function createNewCar(Car $car): Response
    {
        $errors = $this->validator->validate($car);
        if (count($errors) > 0) {
            $errorMessage = 'Invalid input data: ' . (string) $errors;
            $this->logger->error($errorMessage, ['car_data' => $car]);
            return new Response($errorMessage, Response::HTTP_BAD_REQUEST);
        }

        $car->setCreatedAt(new \DateTimeImmutable());

        try {
            $this->entityManager->persist($car);
            $this->entityManager->flush();

            $this->logger->info('Car created successfully', ['car_id' => $car->getId()]);

            return new Response('Car created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Error creating car', [
                'error_message' => $e->getMessage(),
                'car_data' => $car,
            ]);
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    // Update car
    public function updateCar(Car $car): Response
    {
        $errors = $this->validator->validate($car);
        if (count($errors) > 0) {
            $errorMessage = 'Invalid input data: ' . (string) $errors;
            $this->logger->error($errorMessage, ['car_data' => $car]);
            return new Response($errorMessage, Response::HTTP_BAD_REQUEST);
        }

        $car->setUpdatedAt(new \DateTimeImmutable());

        try {
            $this->entityManager->flush();

            $this->logger->info('Car updated successfully', ['car_id' => $car->getId()]);

            return new Response('Car updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Error updating car', [
                'car_id' => $car->getId(),
                'error_message' => $e->getMessage(),
            ]);
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    // Delete car by ID
    public function deleteCarById(int $id): Response
    {
        try {
            $car = $this->entityManager->getRepository(Car::class)->find($id);

            if (!$car) {
                $this->logger->error('Car not found for deletion', ['car_id' => $id]); 
                return new Response('Car not found', Response::HTTP_NOT_FOUND);
            }
            $this->entityManager->remove($car);
            $this->entityManager->flush();

            $this->logger->info('Car deleted successfully', ['car_id' => $id]);

            return new Response('Car deleted successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting car', [
                'car_id' => $id,
                'error_message' => $e->getMessage(),
            ]);
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    // Get cars with expiring registration
    public function getCarsWithExpiringRegistration(): array
    {
        try {
            $currentDate = new DateTimeImmutable();
            $endOfThisMonth = $currentDate->modify('+30 days');

            $this->logger->info('Fetching cars with expiring registration', ['end_date' => $endOfThisMonth]);

            return $this->carRepository->findByRegistrationExpiringUntil($endOfThisMonth);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching cars with expiring registration', [
                'error_message' => $e->getMessage(),
            ]);
            throw $e; 
        }
    }
}
