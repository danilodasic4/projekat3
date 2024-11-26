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
use DateTimeImmutable;
use App\Service\RegistrationCostService;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;

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
    #[OA\Get(
        path: "/cars",
        summary: "Get list of all cars",
        description: "Retrieve all cars",
        tags: ["Car"],
        response: [
            new OA\Response(response: "200", description: "List of cars")
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
    #[OA\Get(
        path: "/cars/{id}",
        summary: "Get car by ID",
        description: "Retrieve a single car by its ID",
        tags: ["Car"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID of the car",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: "200", description: "Car details"),
            new OA\Response(response: "404", description: "Car not found")
        ]
    )]
    public function show(Car $car): Response
    {
        // The $car parameter is automatically resolved by CarValueResolver
        return $this->render('car/show.html.twig', [
            'car' => $car,
        ]);
    }



    // Create a new car (Create)
    #[Route('/cars/create', name: 'app_car_new', methods: ['GET', 'POST'])]
    #[OA\Post(
        path: "/cars/create",
        summary: "Create a new car",
        description: "Create a new car",
        tags: ["Car"],
        requestBody: new OA\RequestBody(
            description: "Car data",
            required: true,
            content: new OA\MediaType(mediaType: "application/json", schema: new OA\Schema(type: "object"))
        ),
        response: [
            new OA\Response(response: "201", description: "Car created successfully")
        ]
    )]
    public function new(Request $request): Response
    {
        $car = new Car(); // Nova instanca automobila
        $form = $this->createForm(CarFormType::class, $car);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $car->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($car);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_car_index'); // Redirekt na listu automobila
        }

        return $this->render('car/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


// Edit an existing car (Update)
#[Route('/cars/update/{id<\d+>}', name: 'app_car_edit', methods: ['GET', 'PUT'])]
#[OA\Put(
    path: "/cars/update/{id}",
    summary: "Update car",
    description: "Update an existing car",
    tags: ["Car"],
    parameters: [
        new OA\Parameter(name: "id", in: "path", description: "ID of the car", required: true, schema: new OA\Schema(type: "integer"))
    ],
    response: [
        new OA\Response(response: "200", description: "Car updated successfully")
    ]
)]
public function edit(Request $request, #[ValueResolver('car')] Car $car): Response
{
    $form = $this->createForm(CarFormType::class, $car);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $car->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->redirectToRoute('app_car_index'); // Redirekt na listu automobila
    }

    return $this->render('car/edit.html.twig', [
        'form' => $form->createView(),
        'car' => $car,
    ]);
}


        // Delete a car (Delete)
    #[Route('/cars/delete/{id<\d+>}', name: 'app_car_delete', methods: ['GET', 'DELETE'])]
    #[OA\Delete(
        path: "/cars/delete/{id}",
        summary: "Delete a car",
        description: "Delete an existing car by ID",
        tags: ["Car"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", description: "ID of the car", required: true, schema: new OA\Schema(type: "integer"))
        ],
        response: [
            new OA\Response(response: "200", description: "Car deleted successfully")
        ]
    )]
    public function delete(Request $request, #[ValueResolver('car')] Car $car): Response
    {
        if ($request->isMethod('DELETE')) {
            $this->entityManager->remove($car);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_car_index'); // Redirekt na listu automobila
        }

        return $this->render('car/delete.html.twig', [
            'car' => $car, // Prikazuje detalje automobila pre potvrde brisanja
        ]);
    }



        // Expiring registration cars (Read)
    #[Route('/cars/expiring-registration', name: 'app_cars_expiring_registration', methods: ['GET'])]
    #[OA\Get(
        path: "/cars/expiring-registration",
        summary: "Get cars with expiring registration",
        description: "Retrieve cars with registration expiring before the end of the current month",
        tags: ["Car"],
        response: [
            new OA\Response(response: "200", description: "List of cars with expiring registration")
        ]
    )]
    public function expiringRegistration(CarRepository $carRepository): Response
    {
        $currentDate = new DateTimeImmutable();
        $endOfThisMonth = $currentDate->modify('last day of this month')->setTime(23, 59, 59);

        // Dohvati automobile čija registracija ističe do kraja meseca
        $cars = $carRepository->findByRegistrationExpiringUntil($endOfThisMonth);

        return $this->render('car/expiring_registration.html.twig', [
            'cars' => $cars, // Prosleđuje listu automobila
        ]);
    }



        // Calculate registration cost for a specific car with a discount code (API endpoint)
        #[Route('/cars/calculate-registration-cost', name: 'car_calculate_registration_cost')]
        #[OA\Post(
            path: "/cars/calculate-registration-cost",
            summary: "Calculate registration cost for a car",
            description: "Calculate registration cost for a car, with an optional discount code",
            tags: ["Car"],
            requestBody: new OA\RequestBody(
                description: "Car ID and optional discount code",
                required: true,
                content: new OA\MediaType(mediaType: "application/x-www-form-urlencoded", schema: new OA\Schema(type: "object", properties: [
                    new OA\Property(property: "carId", type: "integer", description: "ID of the car"),
                    new OA\Property(property: "discountCode", type: "string", description: "Optional discount code")
                ]))
            ),
            response: [
                new OA\Response(response: "200", description: "Registration cost calculated", content: new OA\MediaType(mediaType: "application/json"))
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
    

        #[Route('/cars/registration-details/{id<\d+>}', name: 'app_car_registration_details', methods: ['GET', 'POST'])]
        #[OA\Get(
            path: "/cars/registration-details/{id}",
            summary: "Get registration details for a car",
            description: "Retrieve and update registration details for a car by ID",
            tags: ["Car"],
            parameters: [
                new OA\Parameter(name: "id", in: "path", description: "ID of the car", required: true, schema: new OA\Schema(type: "integer"))
            ],
            response: [
                new OA\Response(response: "200", description: "Registration details of the car")
            ]
        )]
        #[OA\Post(
            path: "/cars/registration-details/{id<\d+>}",
            summary: "Update registration details for a car",
            description: "Submit registration details (with discount code) for updating",
            tags: ["Car"],
            parameters: [
                new OA\Parameter(name: "id", in: "path", description: "ID of the car", required: true, schema: new OA\Schema(type: "integer"))
            ],
            requestBody: new OA\RequestBody(
                description: "Form data with optional discount code",
                required: true,
                content: new OA\MediaType(mediaType: "application/x-www-form-urlencoded", schema: new OA\Schema(type: "object", properties: [
                    new OA\Property(property: "discountCode", type: "string", description: "Optional discount code")
                ]))
            ),
            response: [
                new OA\Response(response: "200", description: "Registration details updated successfully")
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
