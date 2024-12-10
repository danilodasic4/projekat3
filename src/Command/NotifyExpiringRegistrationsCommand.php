<?php
namespace App\Command;

use App\Service\MailService;
use App\Service\CarService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DateTimeImmutable;

class NotifyExpiringRegistrationsCommand extends Command
{
    private CarService $carService;
    private MailService $mailService;

    public function __construct(CarService $carService, MailService $mailService)
    {
        parent::__construct();
        $this->carService = $carService;
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

        // Set the date for cars expiring in the next 30 days
        $endDate = new DateTimeImmutable('+30 days');
        
        // Get users with expiring cars
        $userNotifications = $this->carService->getUsersWithExpiringCars($endDate);

        // Loop through the users and send the email
        foreach ($userNotifications as $email => $cars) {
            $this->mailService->sendExpiringRegistrationEmail($email, $cars);
            $output->writeln("Email sent to: $email for " . count($cars) . " car(s).");
        }

        return Command::SUCCESS;
    }
}