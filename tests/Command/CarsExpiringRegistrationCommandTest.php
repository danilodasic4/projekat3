<?php 
namespace App\Tests\Command;

use App\Command\CarsExpiringRegistrationCommand;
use App\Entity\Car;
use App\Repository\CarRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CarsExpiringRegistrationCommandTest extends TestCase
{
    private $carRepository;
    private $command;

    protected function setUp(): void
    {
        // Mock za CarRepository
        $this->carRepository = $this->createMock(CarRepository::class);

        // Instanciranje komande sa mock repository-em
        $this->command = new CarsExpiringRegistrationCommand($this->carRepository);
    }

    public function testExecuteWithCarsExpiring(): void
    {
        // Pripremamo test podatke
        $car1 = new Car();
        $car1->setBrand('Toyota');
        $car1->setModel('Rav4');
        $car1->setYear(2020);
        $car1->setEngineCapacity(1999);
        $car1->setHorsePower(150);
        $car1->setColor('Red');
        // Datum registracije pre kraja sledećeg meseca
        $car1->setRegistrationDate(new DateTimeImmutable('2025-01-10'));

        $car2 = new Car();
        $car2->setBrand('Toyota');
        $car2->setModel('Corolla');
        $car2->setYear(2020);
        $car2->setEngineCapacity(2000);
        $car2->setHorsePower(150);
        $car2->setColor('Red');
        $car2->setRegistrationDate(new \DateTimeImmutable('2024-12-15'));
        // Datum registracije pre kraja sledećeg meseca
        // Mock metode za dobijanje automobila sa isteklim registracijama
        $this->carRepository->method('findByRegistrationExpiringUntil')
            ->willReturn([$car1, $car2]);

        // Testiranje komande
        $commandTester = new CommandTester($this->command);
        
        // Izvršavanje komande
        $commandTester->execute([]);

        // Dobijeni izlaz komande
        $output = $commandTester->getDisplay();

        // Debugging: štampanje izlaza komande za proveru
        echo "Command Output:\n" . $output . "\n"; 

        // Provera da li izlaz sadrži očekivanu poruku
        $this->assertStringContainsString('Cars with Registration Expiring Before Next Month', $output);
        $this->assertStringContainsString('Toyota', $output);
        $this->assertStringContainsString('Toyota', $output);
    }

    public function testExecuteWithNoCarsExpiring(): void
    {
        // Mock za nepostojanje automobila sa isteklim registracijama
        $this->carRepository->method('findByRegistrationExpiringUntil')
            ->willReturn([]);

        // Testiranje komande
        $commandTester = new CommandTester($this->command);
        
        // Izvršavanje komande
        $commandTester->execute([]);

        // Dobijeni izlaz komande
        $output = $commandTester->getDisplay();

        // Debugging: štampanje izlaza komande za proveru
        echo "Command Output:\n" . $output . "\n"; 

        // Provera da li izlaz sadrži poruku kada nema automobila
        $this->assertStringContainsString('No cars with expiring registrations found.', $output);
    }
}

