<?php

namespace App\DependencyInjection;

use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\RegistrationCostService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\CarService;
use Symfony\Component\Security\Core\Security;
use App\Service\SchedulingService;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class CarControllerDependencies
{
    public function __construct(
        public CarRepository $carRepository, 
        public EntityManagerInterface $entityManager, 
        public RegistrationCostService $registrationCostService,
        public HttpClientInterface $httpClient,
        public CarService $carService,
        public Security $security,
        public SchedulingService $schedulingService,
        public MessageBusInterface $messageBus,
        public string $apiHost,
    ) {}
}
