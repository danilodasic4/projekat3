<?php
namespace App\Enum; 

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum AppointmentTypeEnum: string implements TranslatableInterface
{
    case MAINTENANCE = 'maintenance';
    case REGISTRATION = 'registration';
    case POLISHING = 'polishing';
    case PAINTING = 'painting';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::MAINTENANCE => $translator->trans('appointment.type.maintenance', locale: $locale),
            self::REGISTRATION => $translator->trans('appointment.type.registration', locale: $locale),
            self::POLISHING => $translator->trans('appointment.type.polishing', locale: $locale),
            self::PAINTING => $translator->trans('appointment.type.painting', locale: $locale),
        };
    }
   
}
