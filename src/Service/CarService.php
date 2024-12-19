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
use App\Exception\UnauthorizedCarAccessException;

class CarService
{
    public function __construct(
        private readonly CarRepository $carRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger,
        private readonly Security $security, 
        private readonly string $apiHost,
 )  {

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

    if (isset($rawData['error'])) {
        $this->logger->info('No cars found for this user.', ['user_id' => $userId]);
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
    public function expiringRegistration(User $user): array
    {
        try {
            $currentDate = new DateTimeImmutable();
            $endOfThisMonth = $currentDate->modify('+30 days');

            $this->logger->info('Fetching cars with expiring registration', ['end_date' => $endOfThisMonth]);

            return $this->carRepository->findByRegistrationExpiringUntil($user, $endOfThisMonth);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching cars with expiring registration', [
                'error_message' => $e->getMessage(),
            ]);
            throw $e; 
        }
    }
    /**
     * Fetch all cars with registrations expiring until the specified date
     * and group them by user email.
     *
     * @param DateTimeImmutable $endDate
     * @return array
     */
    public function getCarsGroupedByUserWithExpiringRegistration(DateTimeImmutable $endDate): array
    {
        // Fetch cars with registration expiring until the given date
        $cars = $this->carRepository->findAllExpiringUntil($endDate);

        // Group cars by user email
        $groupedCars = [];
        foreach ($cars as $car) {
            $userEmail = $car->getUser()->getEmail();
            if (!isset($groupedCars[$userEmail])) {
                $groupedCars[$userEmail] = [];
            }
            $groupedCars[$userEmail][] = $car;
        }

        return $groupedCars;
    }
    public function assertOwner(Car $car): void
{
    $user = $this->security->getUser();

    if (!$car->getUser() || $car->getUser() !== $user) {
        throw new UnauthorizedCarAccessException();
    }
}

}
