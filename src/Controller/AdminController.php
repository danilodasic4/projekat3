<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

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
    public function appointments(): Response
    {
        // Logika za dohvat svih termina
        $appointments = $this->getDoctrine()
                             ->getRepository(Appointment::class)
                             ->findAll();

        return $this->render('admin/appointments.html.twig', [
            'appointments' => $appointments,
        ]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function users(): Response
    {
        $users = $this->getDoctrine()
                      ->getRepository(User::class)
                      ->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/cars', name: 'admin_cars')]
    public function cars(): Response
    {
        $cars = $this->getDoctrine()
                     ->getRepository(Car::class)
                     ->findAll();

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

}

