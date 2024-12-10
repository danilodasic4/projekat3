<?php
namespace App\Command;

use App\Repository\CarRepository;
use App\Repository\UserRepository;
use App\Service\MailService; 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DateTimeImmutable;

class NotifyExpiringRegistrationsCommand extends Command
{
    private UserRepository $userRepository;
    private CarRepository $carRepository;
    private MailService $mailService;

    public function __construct(UserRepository $userRepository, CarRepository $carRepository, MailService $mailService)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->carRepository = $carRepository;
        $this->mailService = $mailService;
    }

    protected function configure(): void
    {
        $this->setName('app:notify-expiring-registrations')
            ->setDescription('Notifies users about expiring car registrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting to notify users about expiring registrations...');

        $endDate = new DateTimeImmutable('+30 days');
        $usersWithExpiringCars = $this->userRepository->findAll(); 

        $userNotifications = [];
        foreach ($usersWithExpiringCars as $user) {
            $expiringCars = $this->carRepository->findByRegistrationExpiringUntil($user, $endDate);
            if (!empty($expiringCars)) {
                $userNotifications[$user->getEmail()] = $expiringCars;
            }
        }
        foreach ($userNotifications as $email => $cars) {
            $this->mailService->sendExpiringRegistrationEmail($email, $cars);
            $output->writeln("Email sent to: $email for " . count($cars) . " car(s).");
        }

        return Command::SUCCESS;
    }
}