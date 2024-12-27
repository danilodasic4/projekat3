<?php
namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CheckCarHistoryEvent extends Event
{
    public const NAME = 'check_car_history.event';

    public function __construct(
        readonly private int $carId
        ) {}


    public function getCarId(): int
    {
        return $this->carId;
    }
}
