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
use OpenApi\Attributes as OA;

class CarController extends AbstractController
{
    public function __construct(
        private readonly CarRepository $carRepository, 
        private readonly EntityManagerInterface $entityManager, 
        private readonly RegistrationCostService $registrationCostService
    ) {}

    // Show list of all cars (Read)
    #[Route('/cars', name: 'app_car_index', methods: ['GET'])]
    #[OA\Get(
        path: '/cars',
        summary: 'Get the list of all cars',
        description: 'This route returns a list of all cars available.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'A list of cars',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Car')
                )
            )
        ]
    )]
    public function index(): Response
    {
        $cars = $this->carRepository->findAll();

        return $this->render('car/index.html.twig', [
            'cars' => $cars
        ]);
    }

    #[Route('/cars/{id<\d+>}', name: 'app_car_show', methods: ['GET'])]
    #[ParamConverter('car', class: 'App\Entity\Car')]
    #[OA\Get(
        path: '/cars/{id}',
        summary: 'Get car details',
        description: 'This route returns the details of a car by its ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Car ID', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Details of the car',
                content: new OA\JsonContent(ref: '#/components/schemas/Car')
            ),
            new OA\Response(
                response: 404,
                description: 'Car not found'
            )
        ]
    )]
    public function show(Car $car): Response
    public function show(int $id): Response
    {
        $car = $this->carRepository->find($id);

        if (!$car) {
            throw $this->createNotFoundException('Car not found');
        }

        return $this->render('car/show.html.twig', [
            'car' => $car,
        ]);
}


    // Create a new car (Create)
    #[Route('/cars/create', name: 'app_car_new', methods: ['GET', 'POST'])]
    #[OA\Post(
        path: '/cars/create',
        summary: 'Create a new car',
        description: 'This route creates a new car entry.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/Car'
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Car created successfully'),
            new OA\Response(response: 400, description: 'Invalid input data')
        ]
    )]
    public function new(Request $request): Response
    {
        $car = new Car();
        $form = $this->createForm(CarFormType::class, $car);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $car->setCreatedAt(new \DateTimeImmutable());
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
    #[ParamConverter('car', class: 'App\Entity\Car')]
    #[OA\Put(
        path: '/cars/update/{id}',
        summary: 'Update an existing car',
        description: 'This route allows updating the car details by ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Car ID', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/Car')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Car updated successfully'),
            new OA\Response(response: 404, description: 'Car not found'),
            new OA\Response(response: 400, description: 'Invalid input data')
        ]
    )]
    public function edit(Request $request, Car $car): Response
    {
        $form = $this->createForm(CarFormType::class, $car, [
            'method' => 'PUT',
            'action' => $this->generateUrl('app_car_edit', ['id' => $car->getId()]),
        ]);

        $form->handleRequest($request);
#[Route('/cars/update/{id}', name: 'app_car_edit', methods: ['GET', 'PUT'])]
#[ParamConverter('car', class: 'App\Entity\Car')]
public function edit(Request $request, Car $car): Response
{
    if (!$car) {
        throw $this->createNotFoundException('Car not found');
    }

    $form = $this->createForm(CarFormType::class, $car, [
        'method' => 'PUT',
        'action' => $this->generateUrl('app_car_edit', ['id' => $car->getId()])
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
    #[Route('/cars/delete/{id}', name: 'app_car_delete', methods: ['GET', 'POST', 'DELETE'])]
    #[ParamConverter('car', class: 'App\Entity\Car')]
    #[OA\Delete(
        path: '/cars/delete/{id}',
        summary: 'Delete a car',
        description: 'This route allows deleting a car by ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Car ID', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Car deleted successfully'),
            new OA\Response(response: 404, description: 'Car not found')
        ]
    )]
    public function delete(Request $request, Car $car): Response
    {
        if ($request->isMethod('POST') || $request->isMethod('DELETE')) {
            $this->entityManager->remove($car);
            $this->entityManager->flush();
#[Route('/cars/delete/{id}', name: 'app_car_delete', methods: ['GET', 'DELETE'])]
#[ParamConverter('car', class: 'App\Entity\Car')]
public function delete(Request $request, Car $car): Response
{
    if (!$car) {
        throw $this->createNotFoundException('Car not found');
    }

    // Handling the form submission
    if ($request->isMethod('DELETE')) {
        $this->entityManager->remove($car);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_car_index');
    }

    return $this->render('car/delete.html.twig', [
        'car' => $car,
    ]);
}

 // Get list of cars with expiring registration
    #[Route('/cars/expiring-registration', name: 'app_cars_expiring_registration', methods: ['GET'])]
    #[OA\Get(
        path: '/cars/expiring-registration',
        summary: 'Get list of cars with expiring registration',
        description: 'This route returns a list of cars whose registration expires by the end of the current month.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'A list of cars with expiring registration',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Car')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No cars found with expiring registration'
            )
        ]
    )]
    public function expiringRegistration(): Response
    {
        $currentDate = new DateTimeImmutable();
        $endOfThisMonth = $currentDate->modify('last day of this month')->setTime(23, 59, 59);
        
        // Get cars whose registration expires by the end of the current month
        $cars = $this->carRepository->findByRegistrationExpiringUntil($endOfThisMonth);

        // Return HTML page with the list of cars
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
    #[OA\Get(
        path: '/cars/calculate-registration-cost',
        summary: 'Calculate registration cost for a car',
        description: 'This route calculates the registration cost of a car and applies a discount code if provided.',
        parameters: [
            new OA\Parameter(name: 'carId', in: 'query', description: 'Car ID', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'discountCode', in: 'query', description: 'Discount code for the registration', required: false, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Registration cost calculated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'carId', type: 'integer'),
                        new OA\Property(property: 'registrationCost', type: 'number'),
                        new OA\Property(property: 'finalCost', type: 'number')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Car not found')
        ]
    )]
    public function calculateRegistrationCost(Request $request): Response
    {
        $carId = $request->query->get('carId');
        $discountCode = $request->query->get('discountCode'); 

        $car = $this->carRepository->find($carId);

        if (!$car instanceof Car) {
            return $this->json(['error' => 'Car not found'], 404);
        }

        $baseCost = $this->registrationCostService->calculateRegistrationCost($car);
        $finalCost = $this->registrationCostService->applyDiscount($baseCost, $discountCode);

        return $this->json([
            'carId' => $car->getId(),
            'registrationCost' => $baseCost,
            'finalCost' => $finalCost,
        ]);
    }
    #[Route('/cars/registration-details/{id}', name: 'app_car_registration_details', methods: ['GET', 'POST'])]
    #[ParamConverter('car', class: 'App\Entity\Car')]
    #[OA\Get(
        path: '/cars/registration-details/{id}',
        summary: 'Get registration details for a specific car',
        description: 'This route returns the registration details for a car by its ID, including the base cost and the final cost (with discount if provided).',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Car ID', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successfully fetched registration details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'car', ref: '#/components/schemas/Car'),
                        new OA\Property(property: 'baseCost', type: 'number', format: 'float'),
                        new OA\Property(property: 'finalCost', type: 'number', format: 'float')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Car not found')
        ]
    )]
    #[OA\Post(
        path: '/cars/registration-details/{id}',
        summary: 'Update registration details for a specific car with a discount code',
        description: 'This route updates the registration cost for a car with a discount code.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Car ID', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'discountCode', type: 'string', description: 'Optional discount code for the registration')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successfully updated registration details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'car', ref: '#/components/schemas/Car'),
                        new OA\Property(property: 'baseCost', type: 'number', format: 'float'),
                        new OA\Property(property: 'finalCost', type: 'number', format: 'float')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Car not found')
        ]
    )]
    public function registrationDetails(Request $request, Car $car): Response
    {
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
