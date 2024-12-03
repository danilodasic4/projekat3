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
use Psr\Log\LoggerInterface;  

class CarService
{
    private readonly string $apiHost;

    public function __construct(
        private readonly CarRepository $carRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient,
        private readonly Security $security, 
        private readonly LoggerInterface $logger,
        private readonly ValidatorInterface $validator,
        string $apiHost
    ) {
        $this->apiHost = $apiHost;
    }

    public function getCarsForUser(User $user): array
    {
        $cars = $this->carRepository->findBy(['user' => $user]);

        if (empty($cars)) {
            $this->logger->info('No cars found for user.', ['user_id' => $user->getId()]);
            return [];
        }

        $this->logger->info('Cars retrieved for user.', ['user_id' => $user->getId(), 'car_count' => count($cars)]);

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

        $this->logger->info('Fetching cars from external API.', ['user_id' => $userId, 'url' => $url]);

        $response = $this->httpClient->request('GET', $url);

        $content = $response->getContent(false);

        $rawData = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Error parsing JSON data from external API.', ['user_id' => $userId, 'response_content' => $content]);
            return [];
        }

        $this->logger->info('Successfully fetched cars from external API.', ['user_id' => $userId]);

        return $rawData;
    }

    // Get all cars
    public function getAllCars(): array
    {
        $cars = $this->carRepository->findAll();

        $this->logger->info('Retrieved all cars.', ['car_count' => count($cars)]);

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
        $car = $this->carRepository->find($id);

        if ($car) {
            $this->logger->info('Car found by ID.', ['car_id' => $id]);
        } else {
            $this->logger->warning('Car not found by ID.', ['car_id' => $id]);
        }

        return $car;
    }

    public function createNewCar(Car $car): Response
    {
        $errors = $this->validator->validate($car);
        if (count($errors) > 0) {
            $this->logger->error('Validation errors when creating car.', ['car_data' => $car]);
            return new Response('Invalid input data: ' . (string) $errors, Response::HTTP_BAD_REQUEST);
        }

        $car->setCreatedAt(new \DateTimeImmutable());
        
        try {
            $this->entityManager->persist($car);
            $this->entityManager->flush();

            $this->logger->info('Car created successfully.', ['car_id' => $car->getId(), 'user_id' => $car->getUser()->getId()]);

            return new Response('Car created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Error creating car.', ['error' => $e->getMessage()]);
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateCar(Car $car): Response
    {
        $errors = $this->validator->validate($car);
        if (count($errors) > 0) {
            $this->logger->error('Validation errors when updating car.', ['car_data' => $car]);
            return new Response('Invalid input data: ' . (string) $errors, Response::HTTP_BAD_REQUEST);
        }

        $car->setUpdatedAt(new \DateTimeImmutable());

        try {
            $this->entityManager->flush();

            $this->logger->info('Car updated successfully.', ['car_id' => $car->getId()]);

            return new Response('Car updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Error updating car.', ['car_id' => $car->getId(), 'error' => $e->getMessage()]);
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteCarById(int $id): Response
    {
        // Find car by ID
        $car = $this->entityManager->getRepository(Car::class)->find($id);

        if (!$car) {
            $this->logger->warning('Car not found for deletion.', ['car_id' => $id]);
            // Return error response if car is not found
            return new Response('Car not found', Response::HTTP_NOT_FOUND);
        }

        // Remove the car
        $this->entityManager->remove($car);
        $this->entityManager->flush();

        $this->logger->info('Car deleted successfully.', ['car_id' => $id]);

        // Return success response
        return new Response('Car deleted successfully', Response::HTTP_OK);
    }

    public function expiringRegistration(): array
{
    $user = $this->security->getUser();
    if (!$user) {
        $this->logger->info('No user is logged in.');
        return [];
    }
    $this->logger->info('Fetching cars with expiring registration for user.', ['user_id' => $user->getId()]);

    $currentDate = new DateTimeImmutable();
    $endOfThisMonth = $currentDate->modify('last day of this month')->setTime(23, 59, 59);

    // Get cars with registration expiring until the end of this month
    $cars = $this->carRepository->findByRegistrationExpiringUntil($user, $endOfThisMonth);

    if (empty($cars)) {
        $this->logger->info('No cars found with expiring registration.');
    }

    return $cars;
}
}



