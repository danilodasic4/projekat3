<?php
namespace App\EventListener;

use App\Event\CheckCarHistoryEvent;
use App\Repository\CarRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CheckCarHistoryEventHandler implements MessageHandlerInterface
{

    public function __construct(
        private readonly CarRepository $carRepository,
        private readonly ParameterBagInterface $parameterBag, 
    ) {}

    public function __invoke(CheckCarHistoryEvent $event)
    {
        $carId = $event->getCarId();
        
        $cars = $this->carRepository->findAllWithDeletedAt();

        $groupedCars = [];
        foreach ($cars as $car) {
            $key = $car->getBrand() . ' ' . $car->getModel();
            if (!isset($groupedCars[$key])) {
                $groupedCars[$key] = 0;
            }
            $groupedCars[$key]++;
        }

        $this->generateCsvReport($groupedCars);
    }

    private function generateCsvReport(array $groupedCars)
    {
        $filename = 'car_report_' . time() . '.csv'; 
        $filepath = $this->parameterBag->get('kernel.project_dir') . '/public/csv/' . $filename;

        $handle = fopen($filepath, 'w');
        fputcsv($handle, ['Brand and Model', 'Count']); 
        
        foreach ($groupedCars as $car => $count) {
            fputcsv($handle, [$car, $count]);
        }

        fclose($handle);

        return $filename; 
    }
}
