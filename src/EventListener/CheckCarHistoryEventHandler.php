<?php
namespace App\EventListener;

use App\Event\CheckCarHistoryEvent;
use App\Repository\CarRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

readonly class CheckCarHistoryEventHandler implements MessageHandlerInterface
{

    public function __construct(
        private  CarRepository $carRepository,
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
        $filepath = $this->csvDirectory . '/' . $filename;

        $handle = fopen($filepath, 'w');
        fputcsv($handle, ['Brand and Model', 'Count']); 
        
        foreach ($groupedCars as $car => $count) {
            fputcsv($handle, [$car, $count]);
        }

        fclose($handle);

        return $filename; 
    }
}