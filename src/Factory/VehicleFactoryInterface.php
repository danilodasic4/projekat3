<?php

namespace App\Factory;

use App\Entity\AbstractVehicle;
use App\Entity\Car;
use App\Entity\User;

interface VehicleFactoryInterface
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
    ): AbstractVehicle;
}


