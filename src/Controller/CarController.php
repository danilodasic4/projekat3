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

class CarController extends AbstractController
{
    private $carRepository;
    private $entityManager;

    // Constructor for injection konstruktor CarRepository and EntityManagerInterface
    public function __construct(CarRepository $carRepository, EntityManagerInterface $entityManager)
    {
        $this->carRepository = $carRepository;
        $this->entityManager = $entityManager;
    }

    // Show list of all cars (Read)
    #[Route('/cars', name: 'app_car_index', methods: ['GET'])]
    public function index(): Response
    {
        $cars = $this->carRepository->findAll(); // // Using fundamental findAll method 

        return $this->render('car/index.html.twig', [
            'cars' => $cars
        ]);
    }

    // Show details of a specific car (Read)
    #[Route('/cars/{id<\d+>}', name: 'app_car_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $car = $this->carRepository->find($id); // Using fundamental find method 

        if (!$car) {
            throw $this->createNotFoundException('Car not found');
        }

        return $this->render('car/show.html.twig', [
            'car' => $car
        ]);
    }

    // Create a new car (Create)
    #[Route('/cars/new', name: 'app_car_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $car = new Car();
        $form = $this->createForm(CarFormType::class, $car);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set creation date
            $car->setCreatedAt(new \DateTimeImmutable()); 

            // Optionally set the user if it's a logged-in user
            // $car->setUserId($this->getUser());
            
            $this->entityManager->persist($car);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_car_index');
        }

        return $this->render('car/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Edit an existing car (Update)
    #[Route('/cars/{id}/edit', name: 'app_car_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $car = $this->carRepository->find($id); // Using fundamental find method 

        if (!$car) {
            throw $this->createNotFoundException('Car not found');
        }

        $form = $this->createForm(CarFormType::class, $car);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set update time
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
    #[Route('/cars/{id}/delete', name: 'app_car_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $car = $this->carRepository->find($id);
    
        if (!$car) {
            throw $this->createNotFoundException('Car not found');
        }
    
        // Hard delete
        $this->entityManager->remove($car);
        $this->entityManager->flush();
    
        return $this->redirectToRoute('app_car_index');
    }
    
}
