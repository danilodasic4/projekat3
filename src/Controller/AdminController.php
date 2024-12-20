<?php
namespace App\Controller;

use App\Repository\CarRepository;
use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_index')]
    #[IsGranted('ROLE_ADMIN')]  
    public function index(): Response
    {
        $user = $this->getUser();  
    
        return $this->render('admin/index.html.twig', [
            'user' => $user, 
        ]);
    }
    
    #[Route('/admin/appointments', name: 'admin_appointments')]
    public function appointments(AppointmentRepository $appointmentRepository): Response
    {
        $appointments = $appointmentRepository->findAll();

        return $this->render('admin/appointments.html.twig', [
            'appointments' => $appointments,
        ]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/cars', name: 'admin_cars')]
    public function cars(CarRepository $carRepository): Response
    {
        $cars = $carRepository->findAll();

        return $this->render('admin/cars.html.twig', [
            'cars' => $cars,
        ]);
    }

    #[Route('/admin/login', name: 'admin_login')]
    public function login(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/login.html.twig');
    }
    #[Route('/admin/logout', name: 'admin_logout')]
    public function logout(): RedirectResponse
    {
        return $this->redirectToRoute('homepage');
    }
}
