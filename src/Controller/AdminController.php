<?php

namespace App\Controller;
use App\Repository\CarRepository;
use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use InvalidArgumentException; 

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }


    #[Route('/admin/appointments', name: 'admin_appointments_upcoming')]
    public function upcomingAppointments(
        Request $request,
        AppointmentRepository $appointmentRepository,
        CarRepository $carRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator

    ): Response {
        // Get filter parameters from query string
        $carId = $request->query->get('car');
        $userId = $request->query->get('user');
        $appointmentType = $request->query->get('appointment_type');
        $scheduledAtString = $request->query->get('scheduled_at');
        
        $carIdConstraint = new Assert\Optional(new Assert\Type('integer'));
        $userIdConstraint = new Assert\Optional(new Assert\Type('integer'));

        $carIdViolations = $validator->validate($carId, $carIdConstraint);
        $userIdViolations = $validator->validate($userId, $userIdConstraint);

        if (count($carIdViolations) > 0 || count($userIdViolations) > 0) {
            throw new \InvalidArgumentException('carId and userId must be integers.');
        }

        // Check if a date is provided and convert to DateTime object
        $scheduledAt = null;
        if ($scheduledAtString) {
            // Handle the date format correctly (yyyy-mm-dd)
            $scheduledAt = \DateTime::createFromFormat('Y-m-d', $scheduledAtString);
            if (!$scheduledAt) {
                // If it fails, you can handle error or try another format (e.g. mm/dd/yyyy)
                $scheduledAt = \DateTime::createFromFormat('m/d/Y', $scheduledAtString);
            }
        }
    
        // Ensure carId and userId are integers or null
        $carId = $carId ? (int) $carId : null;
        $userId = $userId ? (int) $userId : null;
    
        // Create the filtered query builder
        $queryBuilder = $appointmentRepository->createFilteredQueryBuilder($carId, $userId, $appointmentType, $scheduledAt);
    
        // Filter for upcoming appointments (future dates)
        $queryBuilder->andWhere('a.scheduledAt > :now')
                     ->setParameter('now', new \DateTime());
    
        // Create a Pagerfanta object for pagination
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage($adapter, $request->query->getInt('page', 1), 10);
        
        // Render the upcoming appointments page with pagination
        return $this->render('admin/appointments_upcoming.html.twig', [
            'pager' => $pagerfanta,
            'cars' => $carRepository->findAll(),
            'users' => $userRepository->findAll(),
        ]);
    }
    
        #[Route('/admin/appointments/archived', name: 'admin_appointments_archived')]
    public function archivedAppointments(
        Request $request,
        AppointmentRepository $appointmentRepository,
        CarRepository $carRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator

    ): Response {
        $carId = $request->query->get('car');
        $userId = $request->query->get('user');
        $appointmentType = $request->query->get('appointment_type');
        $scheduledAtString = $request->query->get('scheduled_at');
        
        $carIdConstraint = new Assert\Optional(new Assert\Type('integer'));
        $userIdConstraint = new Assert\Optional(new Assert\Type('integer'));

        $carIdViolations = $validator->validate($carId, $carIdConstraint);
        $userIdViolations = $validator->validate($userId, $userIdConstraint);

        if (count($carIdViolations) > 0 || count($userIdViolations) > 0) {
            throw new \InvalidArgumentException('carId and userId must be integers.');
        }

        $scheduledAt = null;
        if ($scheduledAtString) {
            $scheduledAt = \DateTime::createFromFormat('Y-m-d', $scheduledAtString);
            if (!$scheduledAt) {
                $scheduledAt = \DateTime::createFromFormat('m/d/Y', $scheduledAtString);
            }
        }
    
        $carId = $carId ? (int) $carId : null;
        $userId = $userId ? (int) $userId : null;
    
        $queryBuilder = $appointmentRepository->createFilteredQueryBuilder($carId, $userId, $appointmentType, $scheduledAt);
    
        $queryBuilder->andWhere('a.scheduledAt < :now')
                     ->setParameter('now', new \DateTime());
    
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage($adapter, $request->query->getInt('page', 1), 10);
    
        return $this->render('admin/appointments_archived.html.twig', [
            'pager' => $pagerfanta,
            'cars' => $carRepository->findAll(),
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/admin/cars', name: 'admin_cars')]
    public function cars(CarRepository $carRepository): Response
    {
        return $this->render('admin/cars.html.twig', [
            'cars' => $carRepository->findAll(),
        ]);
    }

    #[Route('/admin/login', name: 'admin_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/admin/logout', name: 'admin_logout', methods: ['GET'])]
    public function logout(SessionInterface $session): RedirectResponse
    {
        $session->clear();
        $session->invalidate();

        return new RedirectResponse($this->generateUrl('homepage'));
    }
}

