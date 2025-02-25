<?php

namespace App\Factory;

use App\Entity\AbstractVehicle;
use App\Entity\Car;
use App\Entity\User;

class CarFactory implements VehicleFactoryInterface
{
    public function create(
        string $brand,
        string $model,
        int $year,
        int $engineCapacity,
        int $horsePower,
        string $color,
        User $user,
        \DateTime $registrationDate
    ): AbstractVehicle {
        $car = new Car();
        $car->setBrand($brand);
        $car->setModel($model);
        $car->setYear($year);
        $car->setEngineCapacity($engineCapacity);
        $car->setHorsePower($horsePower);
        $car->setColor($color);
        $car->setUser($user);
        $car->setRegistrationDate($registrationDate);

        return $car;
    }
}
