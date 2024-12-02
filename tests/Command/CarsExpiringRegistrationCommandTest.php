<?php 
// namespace App\Tests\Command;

// use App\Command\CarsExpiringRegistrationCommand;
// use App\Entity\Car;
// use App\Repository\CarRepository;
// use DateTimeImmutable;
// use PHPUnit\Framework\TestCase;
// use Symfony\Component\Console\Tester\CommandTester;

// class CarsExpiringRegistrationCommandTest extends TestCase
// {
//     private $carRepository;
//     private $command;

//     protected function setUp(): void
//     {
//         // Create mock for CarRepository
//         $this->carRepository = $this->createMock(CarRepository::class);

//         // Instantiate the command with the mock repository
//         $this->command = new CarsExpiringRegistrationCommand($this->carRepository);
//     }

//     public function testExecuteWithCarsExpiring(): void
// {
//     // Prepare test data
//     $car1 = new Car();
//     $car1->setBrand('Toyota');
//     $car1->setModel('Corolla');
//     $car1->setYear(2020);
//     $car1->setRegistrationDate(new DateTimeImmutable('2024-01-10'));

//     $car2 = new Car();
//     $car2->setBrand('Honda');
//     $car2->setModel('Civic');
//     $car2->setYear(2019);
//     $car2->setRegistrationDate(new DateTimeImmutable('2024-01-20'));

//     // Mock CarRepository method to return these cars
//     $this->carRepository->method('findByRegistrationExpiringUntil')
//         ->willReturn([$car1, $car2]);

//     // CommandTester for the command
//     $commandTester = new CommandTester($this->command);
    
//     // Execute the command with an empty array for the input argument
//     $commandTester->execute([], []);  // Only pass the input and options as empty arrays

//     // Fetch the output and check if it contains the expected content
//     $output = $commandTester->getDisplay();
    
//     // Debugging: print the output to see what is returned
//     echo "Command Output:\n" . $output . "\n"; // Add this line to see the output for debugging
    
//     // Assert the string with the cars' brands, checking for successful output
//     $this->assertStringContainsString('Cars with Registration Expiring Before Next Month', $output);
//     $this->assertStringContainsString('Toyota', $output);
//     $this->assertStringContainsString('Honda', $output);
// }

// public function testExecuteWithNoCarsExpiring(): void
// {
//     // Mock to return no cars
//     $this->carRepository->method('findByRegistrationExpiringUntil')
//         ->willReturn([]);

//     // CommandTester for the command
//     $commandTester = new CommandTester($this->command);
    
//     // Execute the command with an empty array for the input argument
//     $commandTester->execute([], []);  // Only pass the input and options as empty arrays

//     // Fetch the output and check if it contains the expected message when no cars are found
//     $output = $commandTester->getDisplay();
    
//     // Debugging: print the output to see what is returned
//     echo "Command Output:\n" . $output . "\n"; // Add this line to see the output for debugging
    
//     // Assert the "no cars" message
//     $this->assertStringContainsString('No cars with expiring registrations found.', $output);
// }
// }
