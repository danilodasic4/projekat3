<?php
namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class AdminFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        $existingAdmin = $this->entityManager->getRepository(Admin::class)->findOneBy(['email' => 'admin@example.com']);

        if ($existingAdmin) {
            $admin2 = new Admin();
            $admin2->setEmail('admin2@example.com');
            $admin2->setRoles(['ROLE_ADMIN']);
            
            $hashedPassword = $this->passwordHasher->hashPassword($admin2, 'admin123');
            $admin2->setPassword($hashedPassword);
            
            $manager->persist($admin2);
        } else {
            $admin = new Admin();
            $admin->setEmail('admin@example.com');
            $admin->setRoles(['ROLE_ADMIN']);
            
            $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
            $admin->setPassword($hashedPassword);
            
            $manager->persist($admin);
        }

        $manager->flush();
    }
}


