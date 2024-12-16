<?php
namespace App\Controller;

use App\Entity\Appointment;
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

class AppointmentController extends AbstractController
{
    public function __construct(
        private readonly SchedulingService $schedulingService,
        private readonly Security $security,

    ) {}
        #[Route('/car/{id}/appointment', name: 'car_create_appointment', methods: ['GET', 'POST'])]
        public function createAppointment(
        #[ValueResolver(CarValueResolver::class)] Car $car,
        #[ValueResolver(UserValueResolver::class)] User $user,
        Request $request
    ): Response {
        $appointment = new Appointment();
        $appointment->setCar($car);
        $appointment->setUser($user);

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

    #[Route('/api/appointments', name: 'api_user_appointments', methods: ['GET'])]
    public function getUserAppointments(AppointmentRepository $appointmentRepository): JsonResponse
    {
        $user = $this->security->getUser();
        $appointments = $appointmentRepository->findBy(['user' => $user]);

        $appointmentData = array_map(function (Appointment $appointment) {
            return [
                'id' => $appointment->getId(),
                'car' => $appointment->getCar()->getId(),
                'scheduledAt' => $appointment->getScheduledAt()->format('Y-m-d H:i:s'),
                'appointmentType' => $appointment->getAppointmentType()->value,
            ];
        }, $appointments);

        return new JsonResponse($appointmentData);
    }
}
