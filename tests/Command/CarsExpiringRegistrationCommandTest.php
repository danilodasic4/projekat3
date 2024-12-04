<?php

namespace App\Tests\Command;

use App\Entity\User;
use App\Command\CarsExpiringRegistrationCommand;
use App\Repository\CarRepository;
use App\Entity\Car;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use DateTimeImmutable;
use Symfony\Component\Security\Core\Security;

class CarsExpiringRegistrationCommandTest extends KernelTestCase
{
    private CarRepository $carRepositoryMock;
    private CommandTester $commandTester;
    private $securityMock;

    protected function setUp(): void
    {
        self::bootKernel();

        // Create a mock for the CarRepository
        $this->carRepositoryMock = $this->createMock(CarRepository::class);

        // Get the command service from the container
        $command = self::getContainer()->get(CarsExpiringRegistrationCommand::class);

        // Inject the mocked repository into the command via reflection
        $commandReflection = new \ReflectionClass(CarsExpiringRegistrationCommand::class);
        $property = $commandReflection->getProperty('carRepository');
        $property->setAccessible(true);
        $property->setValue($command, $this->carRepositoryMock);

        // Create the CommandTester instance
        $this->commandTester = new CommandTester($command);

        // Mock the Security service
        $this->securityMock = $this->createMock(Security::class);

        // Create a user object
        $user = new User();
        $user->setEmail('user1@example.com');
        $user->setPassword('user123'); // Use the same password as in your test data

        // Mock the Security service to return this user when getUser() is called
        $this->securityMock
            ->method('getUser')
            ->willReturn($user);

        // Set the mocked security service in the container
        self::getContainer()->set('security.helper', $this->securityMock);
    }

    public function testExecuteWithCars(): void
    {
        // Create a car associated with the user (simulating a car registration close to expiring)
        $car = new Car();
        $car->setBrand('Tesla')
            ->setModel('Model S')
            ->setYear(2020)
            ->setEngineCapacity(1500)
            ->setHorsePower(350)
            ->setColor('Red')
            ->setRegistrationDate(new DateTimeImmutable('2024-12-01'))
            ->setUser($this->securityMock->getUser()); // Associate car with user

        // Configure the mock to return the car when the correct method is called (use correct method name)
        $this->carRepositoryMock
            ->expects($this->once())
            ->method('findByRegistrationExpiringUntil')
            ->with($this->securityMock->getUser(), $this->anything()) // Match user and expiration date
            ->willReturn([$car]);

        // Run the command
        $this->commandTester->execute([]);

        // Assert the output contains the expected car details
        $output = $this->commandTester->getDisplay();

        // Check that the output contains the expected car details
        $this->assertStringContainsString('Tesla', $output);
        $this->assertStringContainsString('Model S', $output);
        $this->assertStringContainsString('2020', $output);
        $this->assertStringContainsString('1500', $output);  // Engine Capacity
        $this->assertStringContainsString('350', $output);   // Horse Power
        $this->assertStringContainsString('Red', $output);    // Color
       $this->assertStringContainsString('user1@example.com', $output);  // User Email

        // Assert command success
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithNoCars(): void
    {
        // Create a user (simulating a logged-in user)
        $user = new User();
        $user->setEmail('user2@example.com');
        $user->setPassword('user123');  // Use the same password as in your test data

        // Configure the mock to return no cars for the logged-in user
        $this->carRepositoryMock
            ->expects($this->once())
            ->method('findByRegistrationExpiringUntil')
            ->with($user, $this->anything())  // Match user and DateTime condition
            ->willReturn([]);

        // Run the command
        $this->commandTester->execute([]);

        // Assert the output contains the "no cars found" message
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('No cars with expiring registrations found.', $output);

        // Assert command success
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }
}
