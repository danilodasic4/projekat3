<?php
namespace App\Service;

use App\Entity\User;
use App\Exception\ProfilePictureUploadException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RegistrationService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private string $profilePicturesDirectory;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ParameterBagInterface $params
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->profilePicturesDirectory = $params->get('profile_pictures_directory');
    }

    public function registerUser(User $user, ?UploadedFile $profilePicture, array $formData): void
    {
        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $formData['plainPassword']);
        $user->setPassword($hashedPassword);

        // Handle profile picture upload
        if ($profilePicture) {
            try {
                $newFilename = uniqid() . '.' . $profilePicture->guessExtension();
                $profilePicture->move($this->profilePicturesDirectory, $newFilename);
                $user->setProfilePicture($newFilename);
            } catch (FileException $e) {
                throw new ProfilePictureUploadException('Error uploading profile picture: ' . $e->getMessage());
            }
        }

        // Set other fields
        $user->setRoles(['ROLE_USER']);
        $user->setBirthday($formData['birthday']);
        $user->setGender($formData['gender']);
        $user->setNewsletter($formData['newsletter']);

        // Save user to database
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
