<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Repository\CarRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AppointmentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('/admin/appointments/{id}/finish', name: 'admin_appointments_finish', methods: ['POST'])]
public function finishAppointment(
    #[ValueResolver(AppointmentValueResolver::class)] Appointment $appointment 
): JsonResponse
{
    if (!$appointment) {
        return new JsonResponse(['status' => 'error', 'message' => 'Appointment not found'], 404);
    }

    $appointment->setFinishedAt(new \DateTime());
    $this->entityManager->flush();
    
    return new JsonResponse(['status' => 'success', 'message' => 'Appointment finished']);
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
    
        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->lt('a.scheduledAt', ':now'),
                $queryBuilder->expr()->isNotNull('a.finishedAt')
            )
        )
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
}