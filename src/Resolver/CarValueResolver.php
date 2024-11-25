<?php
namespace App\Resolver;

use App\Repository\CarRepository;
use App\Entity\Car;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CarValueResolver implements ValueResolverInterface
{
    private CarRepository $carRepository;

    public function __construct(CarRepository $carRepository)
    {
        $this->carRepository = $carRepository;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $carId = $request->attributes->get('id');

        if (null === $carId) {
            throw new BadRequestException('Car ID is required');
        }

        $car = $this->carRepository->find($carId);

        if (!$car) {
            throw new BadRequestException('Car not found');
        }

        yield $car;
    }
}
