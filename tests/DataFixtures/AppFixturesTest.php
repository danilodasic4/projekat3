<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\AppFixtures;
use App\Entity\Car;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;

class AppFixturesTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testAppFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new AppFixtures(self::getContainer()->get('security.password_hasher')));

        $executor = new ORMExecutor($this->entityManager);
        $executor->execute($loader->getFixtures(), true);

        $userRepository = $this->entityManager->getRepository(User::class);
        $user1 = $userRepository->findOneBy(['email' => 'user1@example.com']);
        $user2 = $userRepository->findOneBy(['email' => 'user2@example.com']);
        $user3 = $userRepository->findOneBy(['email' => 'user3@example.com']);

        $this->assertNotNull($user1, 'User 1 should exist in the database');
        $this->assertNotNull($user2, 'User 2 should exist in the database');
        $this->assertNotNull($user3, 'User 3 should exist in the database');

        $carRepository = $this->entityManager->getRepository(Car::class);
        $cars = $carRepository->findAll();

        $this->assertCount(10, $cars, 'There should be 10 cars in the database');

        foreach ($cars as $car) {
            $this->assertNotNull($car->getUser(), 'Car should be assigned to a user');
            $this->assertInstanceOf(User::class, $car->getUser(), 'The assigned user should be an instance of User');
        }

        foreach ($cars as $car) {
            $this->assertNotNull($car->getBrand(), 'Car brand should be set');
            $this->assertNotNull($car->getModel(), 'Car model should be set');
            $this->assertNotNull($car->getColor(), 'Car color should be set');
        }
    }
}
