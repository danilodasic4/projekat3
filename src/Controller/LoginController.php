<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginController extends AbstractController
{

    #[Route('/login', name: 'app_login', methods: ['GET','POST'])]
    #[OA\Get(
        path: '/login',
        summary: 'User Login (GET)',
        description: 'This route is used to display the login form or redirects if the user is already logged in.',
        responses: [
            new OA\Response(response: 200, description: 'Login page loaded successfully'),
            new OA\Response(response: 302, description: 'Redirected to car index page if already logged in')
        ]
    )]
    #[OA\Post(
        path: '/login',
        summary: 'User Login (POST)',
        description: 'This route is used to authenticate the user and process login credentials.',
        requestBody: new OA\RequestBody(
            description: 'User credentials',
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: '_username', type: 'string', example: 'johndoe'),
                    new OA\Property(property: '_password', type: 'string', example: 'securepassword'),
                    new OA\Property(property: '_remember_me', type: 'boolean', example: true)
                    ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login successful, user redirected'),
            new OA\Response(response: 401, description: 'Invalid credentials')
        ]
    )]
        public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Check if the user is already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_car_index'); // Redirect to car list if already logged in
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Last username entered by the user (to pre-fill the username field)
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    #[OA\Get(
        path: '/logout',
        summary: 'User Logout',
        description: 'This route is used to log the user out.',
        responses: [
            new OA\Response(response: 200, description: 'User logged out successfully')
        ]
    )]
    public function logout(SessionInterface $session): RedirectResponse
{
    $session->clear();
    
    $session->invalidate();

    return new RedirectResponse($this->generateUrl('homepage'));
}
}
