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

class CarController extends AbstractController
{
    private $carRepository;
    private $entityManager;

    // Construct for injection CarRepository and EntityManagerInterface
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
    #[ParamConverter('car', class: 'App\Entity\Car')]
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
public function edit(Request $request, int $id): Response
{
    $car = $this->carRepository->find($id);

    if (!$car) {
        throw $this->createNotFoundException('Car not found');
    }
    $form = $this->createForm(CarFormType::class, $car, [
        'method' => 'PUT',
        'action' => $this->generateUrl('app_car_edit',['id' => $id])
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
      public function delete(Request $request, int $id): Response
      {
          $car = $this->carRepository->find($id);
  
          if (!$car) {
              throw $this->createNotFoundException('Car not found');
          }
  
          // Handling the form submission
          if ($request->isMethod('POST') || $request->isMethod('DELETE')) {
              $this->entityManager->remove($car);
              $this->entityManager->flush();
  
              return $this->redirectToRoute('app_car_index');
          }
  
          return $this->render('car/delete.html.twig', [
              'car' => $car,
          ]);
      }
      #[Route('/cars/expiring-registration', name: 'app_cars_expiring_registration')]
      public function expiringRegistration(CarRepository $carRepository): Response
      {
          $currentDate = new DateTimeImmutable();
          // Sledeći mesec - prvo postavljanje datuma za poslednji dan ovog meseca
          $endOfThisMonth = $currentDate->modify('last day of this month')->setTime(23, 59, 59);
      
          // Dohvati sve automobile koji imaju registraciju koja ističe do poslednjeg dana ovog meseca
          $cars = $carRepository->findByRegistrationExpiringUntil($endOfThisMonth);
      
          return $this->render('car/expiring_registration.html.twig', [
              'cars' => $cars,
          ]);
      }
      
}
