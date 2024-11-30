<?php
namespace App\Service;

use App\Entity\Car;
use App\Repository\CarRepository;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

class CarService
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly CarRepository $carRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        LoggerInterface $logger // Dodali smo LoggerInterface
    ) {
        $this->logger = $logger; // Injektovanje loggera
    }

    // Get all cars
    public function getAllCars(): array
    {
        $cars = $this->carRepository->findAll();

        $this->logger->info('Fetching all cars'); // Logovanje info poruke

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
            $this->logger->info('Fetching car with ID', ['car_id' => $id]); // Logovanje info poruke

            $car = $this->carRepository->find($id);

            if (!$car) {
                $this->logger->error('Car not found', ['car_id' => $id]); // Logovanje error poruke ako nije pronađen
            }

            return $car;
        } catch (\Exception $e) {
            // Logovanje greške
            $this->logger->error('Error fetching car by ID', [
                'car_id' => $id,
                'error_message' => $e->getMessage(),
            ]);
            throw $e; // Ponovno bacanje greške
        }
    }

    // Create new car
    public function createNewCar(Car $car): Response
    {
        $errors = $this->validator->validate($car);
        if (count($errors) > 0) {
            $errorMessage = 'Invalid input data: ' . (string) $errors;
            $this->logger->error($errorMessage, ['car_data' => $car]); // Logovanje greške prilikom validacije
            return new Response($errorMessage, Response::HTTP_BAD_REQUEST);
        }

        $car->setCreatedAt(new \DateTimeImmutable());

        try {
            $this->entityManager->persist($car);
            $this->entityManager->flush();

            // Logovanje uspešnog kreiranja automobila
            $this->logger->info('Car created successfully', ['car_id' => $car->getId()]);

            return new Response('Car created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Logovanje greške prilikom čuvanja automobila
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
            $this->logger->error($errorMessage, ['car_data' => $car]); // Logovanje greške prilikom validacije
            return new Response($errorMessage, Response::HTTP_BAD_REQUEST);
        }

        $car->setUpdatedAt(new \DateTimeImmutable());

        try {
            $this->entityManager->flush();

            // Logovanje uspešnog ažuriranja automobila
            $this->logger->info('Car updated successfully', ['car_id' => $car->getId()]);

            return new Response('Car updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            // Logovanje greške prilikom ažuriranja
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
                $this->logger->error('Car not found for deletion', ['car_id' => $id]); // Logovanje greške ako nije pronađen
                return new Response('Car not found', Response::HTTP_NOT_FOUND);
            }
            $this->entityManager->remove($car);
            $this->entityManager->flush();

            // Logovanje uspešnog brisanja automobila
            $this->logger->info('Car deleted successfully', ['car_id' => $id]);

            return new Response('Car deleted successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            // Logovanje greške prilikom brisanja
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
            // Logovanje greške u slučaju izuzetka
            $this->logger->error('Error fetching cars with expiring registration', [
                'error_message' => $e->getMessage(),
            ]);
            throw $e; // Ponovno bacanje greške
        }
    }
}
