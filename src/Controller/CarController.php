<?php

namespace App\Controller;

use App\Entity\Car;
use App\Form\CarFormType;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use DateTimeImmutable;
use App\Service\RegistrationCostService;

class CarController extends AbstractController
{
    // Property Promotion applied here with readonly for the $registrationCostService
    public function __construct(
        private readonly CarRepository $carRepository, 
        private readonly EntityManagerInterface $entityManager, 
        private readonly RegistrationCostService $registrationCostService
    ) {}

    // Show list of all cars (Read)
    #[Route('/cars', name: 'app_car_index', methods: ['GET'])]
    public function index(): Response
    {
        $cars = $this->carRepository->findAll(); // Using fundamental findAll method 

        return $this->render('car/index.html.twig', [
            'cars' => $cars
        ]);
    }

    // Show details of a specific car (Read)
    #[Route('/cars/{id<\d+>}', name: 'app_car_show', methods: ['GET'])]
    #[ParamConverter('car', class: 'App\Entity\Car')]  // ParamConverter for automatic entity conversion based on ID
    public function show(Car $car): Response
    {
        return $this->render('car/show.html.twig', [
            'car' => $car,
        ]);
    }

    // Create a new car (Create)
    #[Route('/cars/create', name: 'app_car_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $car = new Car();
        $form = $this->createForm(CarFormType::class, $car);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set creation date
            $car->setCreatedAt(new \DateTimeImmutable()); 

            // Optionally set the user if it's a logged-in user
            // $car->setUser($this->getUser());
            
            $this->entityManager->persist($car);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_car_index');
        }

        return $this->render('car/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Edit an existing car (Update)
    #[Route('/cars/update/{id}', name: 'app_car_edit', methods: ['GET', 'PUT'])]
    #[ParamConverter('car', class: 'App\Entity\Car')]  // ParamConverter for automatic entity conversion based on ID
    public function edit(Request $request, Car $car): Response
    {
        $form = $this->createForm(CarFormType::class, $car, [
            'method' => 'PUT',
            'action' => $this->generateUrl('app_car_edit', ['id' => $car->getId()]),
        ]);
   
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $car->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            return $this->redirectToRoute('app_car_index');
        }

        return $this->render('car/edit.html.twig', [
            'form' => $form->createView(),
            'car' => $car,
        ]);
    }

  // Delete a car (Delete)
    #[Route('/cars/delete/{id}', name: 'app_car_delete', methods: ['GET', 'DELETE'])]
    #[ParamConverter('car', class: 'App\Entity\Car')]  // ParamConverter for automatic entity conversion based on ID
    public function delete(Request $request, Car $car): Response
    {
        // Handling DELETE request
        if ($request->isMethod('DELETE')) {
            // Remove the car from the database
            $this->entityManager->remove($car);
            $this->entityManager->flush();

            // Redirect to car index page after deletion
            return $this->redirectToRoute('app_car_index');
        }

        // If the request is GET, show the confirmation page
        return $this->render('car/delete.html.twig', [
            'car' => $car,
        ]);
    }


    // View cars with expiring registration (Read)
    #[Route('/cars/expiring-registration', name: 'app_cars_expiring_registration')]
    public function expiringRegistration(CarRepository $carRepository): Response
    {
        $currentDate = new DateTimeImmutable();
        $endOfThisMonth = $currentDate->modify('last day of this month')->setTime(23, 59, 59);
        $cars = $carRepository->findByRegistrationExpiringUntil($endOfThisMonth);
      
        return $this->render('car/expiring_registration.html.twig', [
            'cars' => $cars,
        ]);
    }

    // Calculate registration cost for a specific car with a discount code (API endpoint)
    #[Route('/cars/calculate-registration-cost', name: 'car_calculate_registration_cost')]
    public function calculateRegistrationCost(Request $request): Response
    {
        $carId = $request->query->get('carId');
        $discountCode = $request->query->get('discountCode'); 

        $car = $this->carRepository->find($carId);

        if (!$car instanceof Car) {
            return $this->json(['error' => 'Car not found'], 404);
        }

        // Calculate base registration cost
        $baseCost = $this->registrationCostService->calculateRegistrationCost($car);

        // Apply discount if available
        $finalCost = $this->registrationCostService->applyDiscount($baseCost, $discountCode);

        return $this->json([
            'carId' => $car->getId(),
            'registrationCost' => $baseCost,
            'finalCost' => $finalCost,
        ]);
    }

    #[Route('/cars/registration-details/{id}', name: 'app_car_registration_details', methods: ['GET', 'POST'])]
    public function registrationDetails(Request $request, int $id): Response
    {
        $car = $this->carRepository->find($id);

        if (!$car) {
            throw $this->createNotFoundException('Car not found');
        }

        $baseCost = $this->registrationCostService->calculateRegistrationCost($car);

        $discountCode = null;
        $finalCost = $baseCost;

        if ($request->isMethod('POST')) {
            $discountCode = $request->request->get('discountCode');
            $finalCost = $this->registrationCostService->applyDiscount($baseCost, $discountCode);
        }

        return $this->render('car/registration_details.html.twig', [
            'car' => $car,
            'baseCost' => $baseCost,
            'finalCost' => $finalCost,
        ]);
    }
}
