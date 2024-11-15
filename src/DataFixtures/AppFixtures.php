<?php

namespace App\DataFixtures;

use App\Entity\Car;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory; // Using fakerphp/faker
use \DateTime;

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
            $car->setBrand($faker->company)  // Set the car's brand (e.g., Toyota)
                ->setModel($faker->word)     // Set the car's model (e.g., Corolla)
                ->setYear($faker->year)      // Set the car's year of production
                ->setEngineCapacity($faker->numberBetween(1000, 5000))  // Set the engine capacity in cc
                ->setHorsePower($faker->numberBetween(70, 500))         // Set the car's horsepower
                ->setColor($faker->safeColorName)  // Set the car's color (e.g., red, blue)
                ->setUser($user)  // Associate the car with the created user
                ->setCreatedAt($currentDate)  // Set created_at for the car
                ->setUpdatedAt($currentDate)  // Set updated_at for the car
                ->setDeletedAt(null)  // Set deleted_at to null (no soft delete)
                ->setRegistrationDate($faker->dateTimeBetween('-1 years', 'now')); // Random registration date within the last 5 years

            $manager->persist($car);  // Persist each car entity
        }

        // Flush the data to save cars in the database
        $manager->flush();
    }
}
