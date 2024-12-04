<?php
namespace App\Controller;

use App\Entity\User;
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

class RegistrationController extends AbstractController
{  
    
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
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ParameterBagInterface $params): Response
{
    // Create a new User object
    $user = new User();

    // Create the registration form and handle the request
    $form = $this->createForm(RegistrationFormType::class, $user);
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

        // Handle additional form data like birthday, gender, and newsletter preferences
        $user->setBirthday($form->get('birthday')->getData());
        $user->setGender($form->get('gender')->getData());
        $user->setNewsletter($form->get('newsletter')->getData());

        try {
            // Persist the user in the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Add success flash message
            $this->addFlash('success', 'Registration is successful, now you can login!');

            // Redirect to the login page
            return $this->redirectToRoute('app_login');
        } catch (\Exception $e) {
            // Catch any other general exception and display it as a generic error
            $this->addFlash('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    // Render the registration form template
    return $this->render('registration/register.html.twig', [
        'registrationForm' => $form->createView(),
    ]);
}

}

