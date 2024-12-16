<?php

namespace App\Service;

use App\Entity\Car;
use App\Entity\Appointment;
use App\Exception\DuplicateAppointmentException;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

class SchedulingService
{
    public function __construct(
        private readonly AppointmentRepository $appointmentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        )
    {}


    public function scheduleAppointment(Appointment $appointment): void
{
    $appointment->setScheduledAt($this->roundToNearestHalfHour($appointment->getScheduledAt()));

    $existingAppointment = $this->appointmentRepository->findOneBy([
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
public function deleteAppointmentById(int $id): string
{
    try {
        $appointment = $this->entityManager->getRepository(Appointment::class)->find($id);

        if (!$appointment) {
            $this->logger->error('Appointment not found for deletion', ['appointment_id' => $id]);
            return 'Appointment not found';
        }

        $this->entityManager->remove($appointment);
        $this->entityManager->flush();

        $this->logger->info('Appointment deleted successfully', ['appointment_id' => $id]);

        return 'Appointment deleted successfully';
    } catch (\Exception $e) {
        $this->logger->error('Error deleting appointment', [
            'appointment_id' => $id,
            'error_message' => $e->getMessage(),
        ]);
        return 'Error: ' . $e->getMessage();
    }
}


}
