<?php
namespace App\Enum; 

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum AppointmentTypeEnum: string 
{
    case MAINTENANCE = 'maintenance';
    case REGISTRATION = 'registration';
    case POLISHING = 'polishing';
    case PAINTING = 'painting';
}
