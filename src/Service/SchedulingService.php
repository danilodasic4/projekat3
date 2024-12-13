<?php

namespace App\Service;

use App\Entity\Car;
use App\Entity\Appointment;
use App\Exception\DuplicateAppointmentException;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class SchedulingService
{
    private AppointmentRepository $appointmentRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(AppointmentRepository $appointmentRepository, EntityManagerInterface $entityManager)
    {
        $this->appointmentRepository = $appointmentRepository;
        $this->entityManager = $entityManager;
    }


   public function scheduleAppointment(Appointment $appointment): void
   {
       // Check if there's already an appointment at the same time for the car
       $existingAppointment = $this->appointmentRepository->findOneBy([
           'car' => $appointment->getCar(),
           'scheduledAt' => $appointment->getScheduledAt(),
       ]);

       if ($existingAppointment) {
           throw new DuplicateAppointmentException('An appointment is already scheduled for this time.');
       }

       // Proceed with saving the new appointment
       $this->entityManager->persist($appointment);
       $this->entityManager->flush();
   }
   public function getAppointmentsForCar(Car $car): array
    {
        return $this->appointmentRepository->findAppointmentsByCar($car);
    }
}
