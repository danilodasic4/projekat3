<?php
namespace App\Resolver;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class AppointmentValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(private readonly AppointmentRepository $appointmentRepository)
    {}

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === Appointment::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $appointmentId = (int) $request->get('id'); 
        
        $appointment = $this->appointmentRepository->find($appointmentId);

        if (!$appointment) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Appointment not found.');
        }

        yield $appointment;
    }
}
