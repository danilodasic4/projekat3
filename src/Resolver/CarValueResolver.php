<?php
namespace App\Resolver;

use App\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class CarValueResolver implements ValueResolverInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
{
    // Resolver se pokreće samo ako se traži argument tipa Car
    // i ako postoji 'id' u atributima rute
    return $argument->getType() === Car::class && $request->attributes->has('id');
}


    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $carId = $request->attributes->get('id'); // ID iz URL-a
        if (!$carId) {
            throw new \InvalidArgumentException('Car ID is missing.');
        }

        $car = $this->entityManager->getRepository(Car::class)->find($carId);

        if (!$car) {
            throw $this->createNotFoundException('Car not found.');
        }

        yield $car;
    }
}
