<?php
namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CheckCarHistoryEvent extends Event
{
    public const NAME = 'check_car_history.event';

    private $carId;

    public function __construct(int $carId)
    {
        $this->carId = $carId;
    }

    public function getCarId(): int
    {
        return $this->carId;
    }
}
