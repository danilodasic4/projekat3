<?php

namespace App\Enum;

enum AppointmentTypeEnum: string
{
    case MAINTENANCE = 'maintenance';
    case REGISTRATION = 'registration';
    case POLISHING = 'polishing';
    case PAINTING = 'painting';
}
