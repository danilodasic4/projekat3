<?php
namespace App\DataFixtures;

use App\Entity\Car;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory; 
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class AppFixtures extends Fixture
{

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher, 
        private readonly EntityManagerInterface $entityManager
        )
    {}

    public function load(ObjectManager $manager): void
    {
        $existingUsers = $this->entityManager->getRepository(User::class)->findAll();
        if (!empty($existingUsers)) {
            return;
        }

        $faker = Factory::create();

        // Create 3 users
        $users = [];

        for ($i = 0; $i < 3; $i++) {
            $user = new User();
            $user->setEmail('user' . ($i + 1) . '@example.com');  // Set the user's email
            $user->setVerified(true);
            $user->setRoles(['ROLE_VERIFIED']);
            
            // Hash the password "user123"
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'user123');
            $user->setPassword($hashedPassword);

            // Persist the user to the database
            $manager->persist($user);

            // Add the user to the users array for later use
            $users[] = $user;
        }

        // Flush to ensure users are created before creating cars
        $manager->flush();

        // Current date to use for created_at and updated_at fields
        $currentDate = new DateTime();

        // Create 10 cars and assign them randomly to users
        for ($i = 0; $i < 10; $i++) {
            $car = new Car();
            $car->setBrand($faker->company);
            $car->setModel($faker->word);
            $car->setYear($faker->year);
            $car->setEngineCapacity($faker->randomNumber(4));
            $car->setHorsePower($faker->randomNumber(3));
            $car->setColor($faker->colorName);

            // Random registration date within the next year
            $registrationDate = new DateTime('+' . rand(1, 90) . ' days'); 
            $car->setRegistrationDate($registrationDate);

            $car->setCreatedAt(new \DateTime());
            $car->setUpdatedAt(new \DateTime());

            // Randomly assign a user to the car
            $car->setUser($users[rand(0, 2)]);  // Randomly choose a user from the users array

            // Persist the car
            $manager->persist($car);
        }

        // Flush all cars to the database
        $manager->flush();
    }
}
