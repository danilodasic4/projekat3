<?php

namespace App\Exception;

class DuplicateAppointmentException extends \Exception
{
    
     protected $message = 'An appointment already exists at this time for the selected car.';
    
}

