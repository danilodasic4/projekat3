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
        // Pretpostavljamo da se ID automobila prosleđuje kao deo URL-a
        $carId = $request->get('id');

        // Tražimo car entitet u bazi prema ID-u
        $car = $this->entityManager->getRepository(Car::class)->find($carId);

        if (!$car) {
            throw new NotFoundHttpException('Car not found');
        }

        return $car;
    }
}

