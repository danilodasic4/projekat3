<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\VerifyUser;
use App\Exception\ProfilePictureUploadException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\Response;

class RegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager, 
        private readonly UserPasswordHasherInterface $passwordHasher, 
        private readonly ParameterBagInterface $params,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
        private readonly string $appHost,
    ) {}

    public function registerUser(User $user, string $plainPassword, $profilePicture): string
{
    // Set the user password
    $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

    if ($profilePicture) {
        try {
            $this->uploadProfilePicture($user, $profilePicture);
        } catch (ProfilePictureUploadException $e) {
            $this->logger->error($e->getAdditionalErrorInfo());
            // Return a message that the controller can handle
            throw new \RuntimeException($e->getMessage());
        }
    }

    // Set user as unverified initially
    $user->setVerified(false);
    $user->setRoles(['ROLE_USER']); // Initially ROLE_USER

    // Generate verification token
    $token = bin2hex(random_bytes(16));

    // Create and persist VerifyUser entity
    $verifyUser = new VerifyUser();
    $verifyUser->setUser($user);
    $verifyUser->setToken($token);
    $this->entityManager->persist($verifyUser);

    try {
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Send verification email
        $this->sendVerificationEmail($user, $token);

        // Log the successful registration
        $this->logger->info('User registered successfully', ['email' => $user->getEmail()]);

        // Return a message to be handled in the controller
        return 'Registration successful! Please check your email for verification.';
    } catch (\Exception $e) {
        $this->logger->error('Error registering user', ['error' => $e->getMessage(), 'user' => $user->getEmail()]);
        // Return a message to be handled in the controller
        throw new \RuntimeException('An error occurred during registration. Please try again later.');
    }
}

    // Send verification email to user
    private function sendVerificationEmail(User $user, string $token): void
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address('noreply@yourdomain.com', 'Your App'))
                ->to($user->getEmail())
                ->subject('Email verification')
                ->htmlTemplate('emails/verify_email.html.twig')
                ->context([
                    'verifyUrl' => $this->appHost . '/users/' . $user->getId() . '/verify/' . $token,
                    'user' => $user,
                ]);

            $this->mailer->send($email);
            $this->logger->info('Verification email sent', ['email' => $user->getEmail()]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send verification email', [
                'error' => $e->getMessage(),
                'user' => $user->getEmail(),
            ]);
            throw new \RuntimeException('Failed to send verification email. Please try again later.');
        }
    }

    // Verify the user's email via token
    public function verifyUserEmail(User $user, string $token): string
    {
        // Find the verification record by token
        $verifyUser = $this->entityManager->getRepository(VerifyUser::class)->findOneBy([
            'user' => $user,
            'token' => $token,
        ]);
    
        // If verification record is not found, throw an exception
        if (!$verifyUser) {
            throw new \Exception('Invalid verification link.');
        }
    
        // Mark the user as verified
        $user->setVerified(true);
        $user->setRoles(['ROLE_VERIFIED']);
    
        // Remove the VerifyUser entity
        $this->entityManager->remove($verifyUser);
        $this->entityManager->flush();
    
        // Return success message
        return 'Email verified successfully!';
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

