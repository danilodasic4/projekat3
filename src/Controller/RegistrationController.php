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
use OpenApi\Attributes as OA;
use App\Exception\ProfilePictureUploadException;
use App\Service\RegistrationService;

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
    public function register(Request $request, RegistrationService $registrationService): Response
    {
    $user = new User();
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        try {
            $profilePicture = $form->get('profile_picture')->getData();

            if ($profilePicture) {
                $validMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 2 * 1024 * 1024; 
                if (!in_array($profilePicture->getMimeType(), $validMimeTypes)) {
                    $this->addFlash('error', 'Please upload a valid image (JPEG, PNG, GIF).');
                    return $this->redirectToRoute('app_register');
                }

                if ($profilePicture->getSize() > $maxSize) {
                    $this->addFlash('error', 'The file is too large. Maximum allowed size is 2MB.');
                    return $this->redirectToRoute('app_register');
                }
            }

            $formData = [
                'plainPassword' => $form->get('plainPassword')->getData(),
                'birthday' => $form->get('birthday')->getData(),
                'gender' => $form->get('gender')->getData(),
                'newsletter' => $form->get('newsletter')->getData(),
            ];

            $registrationService->registerUser($user, $profilePicture, $formData);

            $this->addFlash('success', 'Registration successful, now you can login!');
            return $this->redirectToRoute('app_login');
            } catch (ProfilePictureUploadException $e) {
            $this->addFlash('error', $e->getMessage());
         }
        }
    return new JsonResponse(['error' => 'Invalid form submission'], Response::HTTP_BAD_REQUEST);
    return $this->render('registration/register.html.twig', [
        'registrationForm' => $form->createView(),
        ]);
    }

}
