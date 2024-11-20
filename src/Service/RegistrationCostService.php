<?php
namespace App\Service;

use App\Entity\Car;

class RegistrationCostService
{
    private $baseCost;

    public function __construct(string $registrationBaseCost)
    {
        $this->baseCost = (int) $registrationBaseCost;
    }

    public function calculateRegistrationCost(Car $car): int
    {
        $year = $car->getYear(); 
        $engineCapacity = $car->getEngineCapacity(); 

        $yearCost = ($year - 1960) * 200; 
        $engineCost = ($engineCapacity - 900) * 200;  

        return $this->baseCost + $yearCost + $engineCost;
    }

    public function applyDiscount(int $cost, string $discountCode): int
    {
        if ($discountCode === 'DISCOUNT2024') {  
            return (int) ($cost * 0.80);  
        }
        return $cost; 
    }
}
