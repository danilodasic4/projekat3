<?php
namespace App\Resolver;

use App\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class CarValueResolver implements ArgumentValueResolverInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === Car::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $carId = $request->get('id');
        
        $car = $this->entityManager->getRepository(Car::class)->find($carId);

        if (!$car) {
            throw new NotFoundHttpException('Car not found');
        }

        yield $car;
    }
}

