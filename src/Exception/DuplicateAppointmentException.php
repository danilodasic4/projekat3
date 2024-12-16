<?php
namespace App\Exception;

use App\Entity\Appointment;

class DuplicateAppointmentException extends \Exception
{
    private Appointment $existingAppointment;

    public function __construct(Appointment $existingAppointment, string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->existingAppointment = $existingAppointment;
        parent::__construct($message, $code, $previous);
    }

    public function getExistingAppointment(): Appointment
    {
        return $this->existingAppointment;
    }
}

