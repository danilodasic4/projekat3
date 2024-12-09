<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
 use App\Service\RegistrationService;

class RegistrationController extends AbstractController
{  
    public function __construct (
        private readonly RegistrationService $registrationService
        ,){}
    
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
    public function register(Request $request, RegistrationService $registrationService): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get password from the form
            $plainPassword = $form->get('plainPassword')->getData();

            // Handle the profile picture (if any)
            $profilePicture = $form->get('profile_picture')->getData();

            try {
                // Register the user
                $this->registrationService->registerUser($user, $plainPassword, $profilePicture);
                
                // Add success message
                $this->addFlash('success', 'Registration is successful, now you can login!');
                
                return $this->redirectToRoute('app_login');
            } catch (ProfilePictureUploadException $e) {
                // Handle profile picture upload error
                $this->addFlash('error', 'Error uploading profile picture: ' . $e->getAdditionalErrorInfo());
            } catch (\Exception $e) {
                // Handle general errors
                $this->addFlash('error', 'An unexpected error occurred: ' . $e->getMessage());
            }
        }

        // Render the registration form
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

}

