<?php
namespace App\Resolver;

use App\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CarValueResolver
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Request $request): Car
    {
        $carId = $request->get('id');
        
        $car = $this->entityManager->getRepository(Car::class)->find($carId);

        if (!$car) {
            throw new NotFoundHttpException('Car not found');
        }

        return $car;
    }
}

