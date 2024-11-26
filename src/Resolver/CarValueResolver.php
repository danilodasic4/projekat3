<?php
namespace App\Resolver;

use App\Entity\Car;
use App\Repository\CarRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class CarValueResolver implements ValueResolverInterface
{
    private CarRepository $carRepository;

    public function __construct(CarRepository $carRepository)
    {
        $this->carRepository = $carRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === Car::class && $request->attributes->has('id');
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
{
    $carId = $request->attributes->get('id');
    dump($carId); // Provera ID-a
    $car = $this->carRepository->find($carId);

    if (!$car) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Car not found with ID $carId.");
    }

    dump($car); // Provera pronađenog entiteta
    yield $car;
}

}
