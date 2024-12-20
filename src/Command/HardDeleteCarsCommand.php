<?php

namespace App\Command;

use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use DateTimeImmutable;


class HardDeleteCarsCommand extends Command
{
    private CarRepository $carRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        CarRepository $carRepository, EntityManagerInterface $entityManager
        )
    {
        parent::__construct();

        $this->carRepository = $carRepository;
        $this->entityManager = $entityManager;
    }
    protected function configure(): void
    {
        $this->setName('app:hard-delete-cars')
            ->setDescription('Hard delete cars that are soft deleted for at least a month.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $carsToDelete = $this->carRepository->findSoftDeletedCarsOlderThan(new DateTimeImmutable('-1 month'));


        if (empty($carsToDelete)) {
            $output->writeln('No cars to hard delete.');
            return Command::SUCCESS;
        }

        foreach ($carsToDelete as $car) {
            try {
                $this->entityManager->remove($car);
                $output->writeln('Hard deleting car with ID ' . $car->getId());
            } catch (OptimisticLockException | ORMException $e) {
                $output->writeln('Error deleting car with ID ' . $car->getId() . ': ' . $e->getMessage());
            }
        }

        $this->entityManager->flush();

        $output->writeln('Hard deletion process completed.');

        return Command::SUCCESS;
    }
}
