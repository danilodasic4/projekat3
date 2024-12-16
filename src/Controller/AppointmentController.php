<?php
namespace App\Controller;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Entity\Car;
use App\Entity\User;
use App\Form\AppointmentType;
use App\Service\SchedulingService;
use App\Repository\CarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Enum\AppointmentTypeEnum; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Exception\DuplicateAppointmentException;
use App\Resolver\CarValueResolver;
use App\Resolver\UserValueResolver;

class AppointmentController extends AbstractController
{
    public function __construct(
        private readonly SchedulingService $schedulingService,
        private readonly Security $security,
        private readonly AppointmentRepository $appointmentRepository,
    ) {}
    #[Route('/car/{id}/appointment', name: 'car_create_appointment', methods: ['GET', 'POST'])]
    public function createAppointment(Car $car, Request $request): Response
    {
        $appointment = new Appointment();

        $appointment->setCar($car);

        $user = $this->security->getUser();
        if ($user) {
            $appointment->setUser($user);
        } else {
            $this->addFlash('error', 'You must be logged in to create an appointment.');
            return $this->redirectToRoute('login');
        }

        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appointment->setCreatedAt(new \DateTime());

            try {
                $this->schedulingService->scheduleAppointment($appointment);

                $this->addFlash('success', 'Appointment created successfully.');
                return $this->redirectToRoute('app_car_index');
            } catch (DuplicateAppointmentException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'An unexpected error occurred. Please try again.');
            }
        }

        return $this->render('appointment/create.html.twig', [
            'form' => $form->createView(),
            'car' => $car,
        ]);
    }

   
    #[Route('/user/{user_id}/appointments', name: 'user_appointments', methods: ['GET'])]
    public function listAppointments(int $user_id): Response
    {
        $appointments = $this->appointmentRepository->findBy(['user' => $user_id]);
    
        return $this->render('appointment/user_appointments.html.twig', [
            'appointments' => $appointments,
            'user_id' => $user_id, 
        ]);
    }
    #[Route('/appointment/delete/{id}', name: 'appointment_delete', methods: ['GET', 'DELETE'])]
    public function delete(int $id): Response
    {
        $result = $this->schedulingService->deleteAppointmentById($id);

        if ($result === 'Appointment deleted successfully') {
            $this->addFlash('success', $result);
        } else {
            $this->addFlash('error', $result);
        }

        return $this->redirectToRoute('user_appointments', [
            'user_id' => $this->getUser()->getId() 
        ]);
    }
}


