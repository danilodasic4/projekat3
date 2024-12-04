<?php
namespace App\Service;

use App\Entity\User;
use App\Exception\ProfilePictureUploadException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;

class RegistrationService
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly UserPasswordHasherInterface $passwordHasher, 
        private readonly ParameterBagInterface $params,
        private readonly LoggerInterface $logger 
    )
    {}

    public function registerUser(User $user, string $plainPassword, ?UploadedFile $profilePicture): void
    {
        // Set the user password
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        
        // Handle the profile picture upload
        if ($profilePicture) {
            $this->uploadProfilePicture($user, $profilePicture);
        }

        // Save the user to the database
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Log the successful registration
            $this->logger->info('User registered successfully', ['email' => $user->getEmail()]);
        } catch (\Exception $e) {
            // Log the error if saving the user fails
            $this->logger->error('Error registering user', ['error' => $e->getMessage(), 'user' => $user->getEmail()]);
            throw $e;  // Rethrow the exception for the controller to handle
        }
    }

    private function uploadProfilePicture(User $user, UploadedFile $profilePicture): void
    {
        $newFilename = uniqid() . '.' . $profilePicture->guessExtension();

        try {
            // Move the uploaded file to the profile pictures directory
            $profilePicture->move(
                $this->params->get('profile_pictures_directory'),
                $newFilename
            );

            // Set the profile picture filename in the user entity
            $user->setProfilePicture($newFilename);
            $this->logger->info('Profile picture uploaded successfully', ['filename' => $newFilename, 'user' => $user->getEmail()]);

        } catch (FileException $e) {
            // Log the error if the file upload fails
            $this->logger->error('Error uploading profile picture', [
                'error' => $e->getMessage(),
                'user' => $user->getEmail(),
                'filename' => $profilePicture->getClientOriginalName()
            ]);

            // Throw a custom exception to handle this error
            throw new ProfilePictureUploadException($profilePicture->getClientOriginalName(), $e->getCode());
        }
    }
}
