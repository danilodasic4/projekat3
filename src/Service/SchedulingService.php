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
    public function __construct(
        private readonly AppointmentRepository $appointmentRepository,
        private readonly EntityManagerInterface $entityManager,
        )
    {}


    public function scheduleAppointment(Appointment $appointment): void
{
    $appointment->setScheduledAt($this->roundToNearestHalfHour($appointment->getScheduledAt()));

    $existingAppointment = $this->appointmentRepository->findOneBy([
        'car' => $appointment->getCar(),
        'scheduledAt' => $appointment->getScheduledAt(),
    ]);
    
    if ($existingAppointment) {
        throw new DuplicateAppointmentException(
            $existingAppointment,
            'An appointment is already scheduled for this time.'
        );
    }

    $this->entityManager->persist($appointment);
    $this->entityManager->flush();
}

    
   public function getAppointmentsForCar(Car $car): array
    {
        return $this->appointmentRepository->findAppointmentsByCar($car);
    }
    
    public function roundToNearestHalfHour(\DateTime $dateTime): \DateTime
{
    $minute = $dateTime->format('i');
    $minute = round($minute / 30) * 30; 
    $dateTime->setTime($dateTime->format('H'), $minute, 0); 
    return $dateTime;
}
}
