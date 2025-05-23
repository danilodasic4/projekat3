<?php
namespace App\Command;

use App\Repository\CarRepository;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:cars-expiring-registration',
    description: 'Get cars with registration expiring before next month'
)]
class CarsExpiringRegistrationCommand extends Command
{
    private CarRepository $carRepository;

    public function __construct(CarRepository $carRepository)
    {
        parent::__construct();
        $this->carRepository = $carRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Current date
        $currentDate = new DateTimeImmutable();

        // Fetch cars whose registration expires in the next month
        $cars = $this->carRepository->findAllExpiringRegistrationsInNextMonth($currentDate);

        if (!$cars) {
            $io->note('No cars with expiring registrations found.');
        } else {
            $io->title('Cars with Registration Expiring Before Next Month');
            $io->table(
                ['Brand', 'Model', 'Year', 'Registration expiration'],
                array_map(function($car) {
                    return [
                        $car->getBrand(),
                        $car->getModel(),
                        $car->getYear(),
                        $car->getRegistrationDate()->format('Y-m-d')
                    ];
                }, $cars)
            );
        }

        return Command::SUCCESS;
    }
}
