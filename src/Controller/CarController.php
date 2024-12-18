<?php

namespace App\Controller;

use App\Entity\Car;
use App\Service\CarService;
use App\Form\CarFormType;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeImmutable;
use App\Service\RegistrationCostService;
use OpenApi\Attributes as OA;
use App\Resolver\CarValueResolver;
use App\Resolver\UserValueResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use App\Service\SchedulingService;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CarController extends AbstractController
{
 public function __construct(
 private readonly CarRepository $carRepository, 
 private readonly EntityManagerInterface $entityManager, 
 private readonly RegistrationCostService $registrationCostService,
 private readonly HttpClientInterface $httpClient,
 private readonly CarService $carService,
 private readonly Security $security,
 private readonly SchedulingService $schedulingService,
 private readonly string $apiHost,
) {}
 
    #[Route('/api/users/{user_id}/cars', name:'api_user_cars', methods:['GET'])]
    #[OA\Get(
        path: '/api/users/{user_id}/cars',
        summary: 'Get the list of cars for a specific user',
        description: 'This route returns a raw JSON list of cars owned by a specific user.',
        parameters: [
            new OA\Parameter(
                name: 'user_id',
                in: 'path',
                required: true,
                description: 'ID of the user',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A raw JSON list of cars for the user',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Car')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found or no cars available for the user'
            )
        ]
    )]
       
    public function getUserCars(
        #[ValueResolver(UserValueResolver::class)] User $user
     ): JsonResponse {
        $cars = $this->carRepository->findBy(['user' => $user]);
       
        if (empty($cars)) {
            return new JsonResponse(['error' => 'No cars found for this user'], Response::HTTP_NOT_FOUND);
        }

        $carData = array_map(function (Car $car) {
            return [
                'id' => $car->getId(),
                'brand' => $car->getBrand(),
                'model' => $car->getModel(),
                'year' => $car->getYear(),
                'color' => $car->getColor(),
            ];
        }, $cars);
       
        return new JsonResponse($carData);
        }


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
        return $this->render('car/index.html.twig', [
            'cars' => $this->carService->getAllCarsForUser($this->security->getUser()->getId()),
            'user' => $this->security->getUser()
        ]);
        }


    #[Route('/cars/{id<\d+>}', name: 'app_car_show', methods: ['GET'])]
    #[OA\Get(
        path: '/cars/{id}',
        summary: 'Get car details',
        description: 'This route returns the details of a car by its ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', description: 'Car ID', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Details of the car', content: new OA\JsonContent(ref: '#/components/schemas/Car')),
            new OA\Response(response: 404,description: 'Car not found')
        ]
    )]
    public function show(#[ValueResolver(CarValueResolver::class)]
        Car $car): Response
        {
        $appointments = $this->schedulingService->getAppointmentsForCar($car);

        return $this->render('car/show.html.twig', [
            'car' => $car,
            'appointments' => $appointments,
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
        public function new(Request $request, Security $security): Response
    {
        $car = new Car();

        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $car->setUser($user);
        $form = $this->createForm(CarFormType::class, $car);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->carService->createNewCar($car);  
            return $this->redirectToRoute('app_car_index'); 
        }

        return $this->render('car/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    // Edit an existing car (Update)
    #[Route('/cars/edit/{id}', name: 'app_car_edit', methods: ['GET'])]
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
    public function edit(#[ValueResolver(CarValueResolver::class)] Car $car): Response
    {
        if (!$car) {
            throw $this->createNotFoundException('Car not found.');
        }
    
        $form = $this->createForm(CarFormType::class, $car, [
            'method' => 'PUT',
            'action' => $this->generateUrl('app_car_update', ['id' => $car->getId()]),
        ]);
        return $this->render('car/edit.html.twig', [
            'car' => $car,
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/cars/update/{id}', name: 'app_car_update', methods: ['PUT'])]
    public function update(#[ValueResolver(CarValueResolver::class)] Car $car, Request $request, ValidatorInterface $validator): Response
    {
        $requestData = $request->request->all()['car_form'];
    
        $constraints = [
            'brand' => [
                new Assert\NotBlank(['message' => 'Brand is required.']),
                new Assert\Length(['max' => 255, 'maxMessage' => 'Brand cannot be longer than 255 characters.']),
            ],
            'model' => [
                new Assert\NotBlank(['message' => 'Model is required.']),
                new Assert\Length(['max' => 255, 'maxMessage' => 'Model cannot be longer than 255 characters.']),
            ],
            'year' => [
                new Assert\NotBlank(['message' => 'Year is required.']),
                new Assert\Range([
                    'min' => 1900,
                    'max' => 2100,
                    'notInRangeMessage' => 'Year must be between {{ min }} and {{ max }}.',
                ]),
            ],
            'engineCapacity' => [
                new Assert\NotBlank(['message' => 'Engine capacity is required.']),
                new Assert\Positive(['message' => 'Engine capacity must be a positive number.']),
            ],
            'horsePower' => [
                new Assert\NotBlank(['message' => 'Horse power is required.']),
                new Assert\Positive(['message' => 'Horse power must be a positive number.']),
            ],
            'color' => [
                new Assert\NotBlank(['message' => 'Color is required.']),
            ],
            'registrationDate' => [
                new Assert\NotBlank(['message' => 'Registration date is required.']),
                new Assert\Date(['message' => 'Invalid registration date format.']),
            ],
        ];
    
        $errorMessages = [];
    
        foreach ($constraints as $field => $fieldConstraints) {
            $violations = $validator->validate($requestData[$field] ?? null, $fieldConstraints);
            foreach ($violations as $violation) {
                $errorMessages[] = $violation->getMessage();
            }
        }
    
        if (!empty($errorMessages)) {
            // Add the error messages as flash messages
            $this->addFlash('form_errors', implode('<br>', $errorMessages)); // Optional: use `<br>` to separate errors in a single message
        
            // Redirect to the car edit page
            return $this->redirectToRoute('app_car_edit', ['id' => $car->getId()]);
        }
    
        $car->setBrand($requestData['brand']);
        $car->setModel($requestData['model']);
        $car->setYear($requestData['year']);
        $car->setEngineCapacity($requestData['engineCapacity']);
        $car->setHorsePower($requestData['horsePower']);
        $car->setColor($requestData['color']);
        try {
            $registrationDate = new \DateTime($requestData['registrationDate']);
            $car->setRegistrationDate($registrationDate);
        } catch (\Exception $e) {
            // Add flash message for invalid date format error
            $this->addFlash('form_errors', ['Invalid registration date format. Please use YYYY-MM-DD.']);
            
            // Redirect back to the edit page
            return $this->redirectToRoute('app_car_edit', ['id' => $car->getId()]);
        }
    
        $this->entityManager->flush();
    
        // Redirect to the car edit page after a successful update
        return $this->redirectToRoute('app_car_edit', ['id' => $car->getId()]);
    }

    #[Route('/cars/delete/{id}', name: 'app_car_delete', methods: ['GET', 'DELETE'])]
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
    public function delete(
        #[ValueResolver(CarValueResolver::class)] Car $car,
        CarService $carService 
    ): Response {
        $response = $carService->deleteCarById($car->getId());

        if ($response->getStatusCode() === Response::HTTP_OK) {
            return $this->redirectToRoute('app_car_index');
        }

        return $this->render('car/delete.html.twig', [
            'error' => $response->getContent(),
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
        return $this->render('car/expiring_registration.html.twig', [
            'cars' => $this->carService->expiringRegistration($this->security->getUser()),
        ]);
    }

// Calculate registration cost for a specific car with a discount code (API endpoint)
#[Route('/cars/calculate-registration-cost', name: 'car_calculate_registration_cost', methods: ['GET'])]
#[OA\Get(
    path: '/cars/calculate-registration-cost',
    summary: 'Calculate registration cost for a car',
    description: 'This route calculates the registration cost of a car and applies a discount code if provided.',
)]
#[OA\Parameter(
    name: 'carId',
    in: 'query',
    description: 'Car ID',
    required: true,
    schema: new OA\Schema(type: 'integer')
)]
#[OA\Parameter(
    name: 'discountCode',
    in: 'query',
    description: 'Discount code for the registration',
    required: true,
    schema: new OA\Schema(type: 'string')
)]
#[OA\Response(
    response: 200,
    description: 'Registration cost calculated',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: 'carId', type: 'integer'),
            new OA\Property(property: 'registrationCost', type: 'number'),
            new OA\Property(property: 'finalCost', type: 'number')
        ]
    )
)]
#[OA\Response(
    response: 404,
    description: 'Car not found'
)]
public function calculateRegistrationCost(Request $request, RegistrationCostService $registrationCostService): Response
{
    // Get query parameters
    $carId = $request->query->get('carId');
    $discountCode = $request->query->get('discountCode', ''); // Default to empty if not provided

    // Find the car by ID
    $car = $this->carRepository->find($carId);

    // If car not found, return error response
    if (!$car instanceof Car) {
        return $this->json(['error' => 'Car not found'], 404);
    }

    // Calculate the base cost for the car registration
    $baseCost = $registrationCostService->calculateRegistrationCost($car);

    // Apply discount code if it's correct
    $finalCost = $registrationCostService->applyDiscount($baseCost, $discountCode);

    // Return JSON response with calculated costs
    return $this->json([
        'carId' => $car->getId(),
        'registrationCost' => $baseCost,
        'finalCost' => $finalCost,
    ]);
}




    #[Route('/cars/registration-details/{id}', name: 'app_car_registration_details', methods: ['GET', 'POST'])]
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
    public function registrationDetails(
        #[ValueResolver(CarValueResolver::class)] Car $car,  
        Request $request,
        RegistrationCostService $registrationCostService
    ): Response {
        if (!$car) {
            throw $this->createNotFoundException('Car not found');
        }
    
        $discountCode = $request->request->get('discountCode', ''); 
        
        $registrationDetails = $registrationCostService->getRegistrationDetails($car, $discountCode);
    
        if ($request->isMethod('POST')) {
            $finalCost = $registrationDetails['finalCost'];
            $baseCost = $registrationDetails['baseCost'];
        } else {
            $baseCost = $registrationDetails['baseCost'];
            $finalCost = $registrationDetails['finalCost'];
        }
    
        return $this->render('car/registration_details.html.twig', [
            'car' => $registrationDetails['car'],
            'baseCost' => $baseCost,
            'finalCost' => $finalCost,
        ]);
    }

}
