<?php

namespace App\DataFixtures;

use App\Entity\Car;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory; // Using fakerphp/faker
use \DateTime;
use DateTimeImmutable;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create a Faker instance to generate fake data
        $faker = Factory::create();

        // Create a new user
        $user = new User();
        $user->setEmail('user@example.com');  // Set the user's email
        $user->setRoles(['ROLE_USER']);        // Assign the user role

        // Hash the password "user123"
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'user123');
        $user->setPassword($hashedPassword);

        // Persist the user to the database
        $manager->persist($user);
        $manager->flush(); // Flush after the user is persisted to make sure the user is saved before creating cars

        // Current date to use for created_at and updated_at fields
        $currentDate = new DateTime();

        // Create 10 cars and associate them with the user
        for ($i = 0; $i < 10; $i++) {
            $car = new Car();
            $car->setBrand($faker->company);
            $car->setModel($faker->word);
            $car->setYear($faker->year);
            $car->setEngineCapacity($faker->randomNumber(4));
            $car->setHorsePower($faker->randomNumber(3));
            $car->setColor($faker->colorName);

            $registrationDate = new DateTimeImmutable('+' . rand(1, 365) . ' days'); // Random datum između 1 i 365 dana od danas
            $car->setRegistrationDate($registrationDate);
            
            $car->setCreatedAt(new \DateTimeImmutable());
            $car->setUpdatedAt(new \DateTimeImmutable());
            
            $car->setUser($user);  

            $manager->persist($car);
        }

        $manager->flush();
    }
}
