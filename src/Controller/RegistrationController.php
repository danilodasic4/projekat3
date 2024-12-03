<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\ProfilePictureUploadException;
use App\Service\RegistrationService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST', 'GET'])]
    public function register(Request $request, RegistrationService $registrationService): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Get profile picture data from the form
                $profilePicture = $form->get('profile_picture')->getData();

                // Handle other form data
                $formData = [
                    'plainPassword' => $form->get('plainPassword')->getData(),
                    'birthday' => $form->get('birthday')->getData(),
                    'gender' => $form->get('gender')->getData(),
                    'newsletter' => $form->get('newsletter')->getData(),
                ];

                // Call the registration service with the data
                $registrationService->registerUser($user, $profilePicture, $formData);

                // If successful, add success flash message
                $this->addFlash('success', 'Registration successful, now you can login!');
                return $this->redirectToRoute('app_login');
            } catch (ProfilePictureUploadException $e) {
                // Catch ProfilePictureUploadException and display error message to user
                $this->addFlash('error', $e->getMessage());
            } catch (\Exception $e) {
                // Catch any other general exception and display it as a generic error
                $this->addFlash('error', 'An unexpected error occurred: ' . $e->getMessage());
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}

