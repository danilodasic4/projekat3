<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\VerifyUser;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Service\RegistrationService;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegistrationController extends AbstractController
{  
    public function __construct (
        private readonly RegistrationService $registrationService
    ) {}

    #[Route('/register', name: 'app_register',methods:['POST','GET'])]
    #[OA\Post(
        path: '/register',
        summary: 'Register a new user',
        description: 'This route allows a new user to register. It accepts form data including username, password, profile picture, and user preferences.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', description: 'User email'),
                    new OA\Property(property: 'plainPassword', type: 'string', description: 'User password'),
                    new OA\Property(property: 'profile_picture', type: 'string', description: 'Profile picture file (optional)', nullable: true),
                    new OA\Property(property: 'birthday', type: 'string', format: 'date', description: 'User birthday (optional)', nullable: true),
                    new OA\Property(property: 'gender', type: 'string', description: 'User gender (optional)', nullable: true, enum: ['male', 'female', 'other']),
                    new OA\Property(property: 'newsletter', type: 'boolean', description: 'User subscription to newsletter (optional)', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User successfully registered',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', description: 'User ID'),
                        new OA\Property(property: 'email', type: 'string', description: 'User email'),
                        new OA\Property(property: 'profile_picture', type: 'string', description: 'Profile picture filename'),
                        new OA\Property(property: 'birthday', type: 'string', format: 'date', description: 'User birthday'),
                        new OA\Property(property: 'gender', type: 'string', description: 'User gender'),
                        new OA\Property(property: 'newsletter', type: 'boolean', description: 'Subscription to newsletter')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input data'),
        ]
    )]
    #[OA\Get(
        path: '/register',
        summary: 'Get registration form',
        description: 'This route returns the registration form for a new user.',
        responses: [
            new OA\Response(response: 200, description: 'Successfully loaded registration form'),
        ]
    )]
        public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $profilePicture = $form->get('profile_picture')->getData();

            try {
                // Call the service to register the user
                $message = $this->registrationService->registerUser($user, $plainPassword, $profilePicture);

                // Show success message via flash
                $this->addFlash('success', $message);

                // Redirect to a success page
                return $this->redirectToRoute('registration_success');
            } catch (ProfilePictureUploadException $e) {
                // Handle the exception specifically for the profile picture upload error
                $this->addFlash('error', 'Error uploading profile picture: ' . $e->getMessage());
            } catch (\Exception $e) {
                // Handle any other general errors
                $this->addFlash('error', 'An unexpected error occurred: ' . $e->getMessage());
            }
        }

        // If the form isn't valid or after handling success/error, render the form again
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/registration/success', name: 'registration_success')]
    public function registrationSuccess(): Response
    {
        return $this->render('registration/success.html.twig');
    }

    // New route for verification email
    #[Route('/users/{userId}/verify/{token}', name: 'verify_email')]
    public function verifyEmail(int $userId, string $token): Response
    {
        try {
            // First calling method from RegistrationService for verification email
            $this->registrationService->verifyUserEmail($userId, $token);

            $this->addFlash('success', $message );
        } catch (\Exception $e) {
            $this->addFlash('error', 'Verification failed: ' . $e->getMessage());
        }

        // Redirection on page with confirming verification
        return $this->redirectToRoute('registration_verified');
    }


    #[Route('/registration/verified', name: 'registration_verified')]
    public function verified(): Response
    {
        return $this->render('registration/verified.html.twig');
    }
}

