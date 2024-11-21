<?php
namespace App\Service;

use App\Entity\Car;

class RegistrationCostService
{
    private int $registrationBaseCost;
    private string $discountCode;

    public function __construct(int $registrationBaseCost, string $discountCode)
    {
        $this->registrationBaseCost = $registrationBaseCost;
        $this->discountCode = $discountCode;
    }

    /**
     * @param Car $car
     * @return float
     */
    public function calculateRegistrationCost(Car $car): float
    {
        return $this->registrationBaseCost + (($car->getYear() - 1960) * 200) + (($car->getEngineCapacity() - 900) * 200);
    }

    /**
     *
     * @param float $cost
     * @param string $inputDiscountCode
     * @return float
     */
    public function applyDiscount(float $cost, string $inputDiscountCode): float
    {
        if ($inputDiscountCode === $this->discountCode) {
            return $cost * 0.8; 
        }

        return $cost; 
    }
}
