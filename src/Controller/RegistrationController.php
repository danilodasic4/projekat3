<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
    {
        // Create a new User instance
        $user = new User();

        // Create the form and bind it to the User entity
        $form = $this->createForm(RegistrationFormType::class, $user);

        // Handle form submission
        $form->handleRequest($request);

        // If the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the user role to 'ROLE_USER'
            $user->setRoles(['ROLE_USER']);
            
            // Handle password encryption
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Handle file upload for profile picture (if provided)
            /** @var UploadedFile $profilePicture */
            $profilePicture = $form->get('profile_picture')->getData();

            if ($profilePicture) {
                // Generate a unique file name based on the current timestamp
                $newFilename = uniqid() . '.' . $profilePicture->guessExtension();

                try {
                    // Move the uploaded file to the designated directory for profile pictures
                    $profilePicture->move(
                        $params->get('profile_pictures_directory'), // Directory for profile pictures
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle error if the file can't be moved
                    $this->addFlash('error', 'There was an error uploading your file.');
                    return $this->redirectToRoute('app_register');
                }

                // Save the file path (just the filename) in the profile_picture field
                $user->setProfilePicture($newFilename);
            }

            // Save the additional fields (birthday, gender, newsletter)
            $birthday = $form->get('birthday')->getData();
            if ($birthday instanceof \DateTimeInterface) {
                $user->setBirthday($birthday); 
            } else {
                $user->setBirthday(null);  
            }

            $user->setGender($form->get('gender')->getData());
            $user->setNewsletter($form->get('newsletter')->getData());

            // Persist the user in the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Show a success message to the user
            $this->addFlash('success', 'Registration is successful, now you can login!');

            // Redirect to the login page
            return $this->redirectToRoute('app_login');
        }

        // Render the registration form template
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
