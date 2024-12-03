<?php
namespace App\Service;

use App\Entity\Car;

class RegistrationCostService
{
     public function __construct(
        private readonly int $registrationBaseCost, 
        private readonly string $discountCode
    ) {}

  
    public function calculateRegistrationCost(Car $car): float
    {
        return $this->registrationBaseCost + (($car->getYear() - 1960) * 200) + (($car->getEngineCapacity() - 900) * 200);
    }

  
    public function applyDiscount(float $cost, string $inputDiscountCode): float
    {
        if ($inputDiscountCode === $this->discountCode) {
            return $cost * 0.8; 
        }

        return $cost; 
    }
        public function getRegistrationDetails(Car $car, ?string $discountCode = null): array
    {
        // Calculate the base registration cost
        $baseCost = $this->calculateRegistrationCost($car);

        // If a discount code is provided, apply it
        $finalCost = $this->applyDiscount($baseCost, $discountCode ?? '');

        return [
            'car' => $car,
            'baseCost' => $baseCost,
            'finalCost' => $finalCost
        ];
    }
}

