<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Lock\LockFactory;

class LoginController extends AbstractController
{
    private const ADMIN_LOCK_KEY = 'ADMIN_LOGGED_IN';
    
    public function __construct(
        private readonly LockFactory $lockFactory
    )
    {}

    #[Route('/admin/login', name: 'admin_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_index');
        }

        $lock = $this->lockFactory->createLock(self::ADMIN_LOCK_KEY, 180, false);

        if (!$lock->acquire(false)) {
            // If the lock is already acquired, show the error template
            return $this->render('admin/login_error.html.twig');
        }

        return $this->render('admin/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/admin/logout', name: 'admin_logout', methods: ['GET'])]
    public function logout(SessionInterface $session): RedirectResponse
    {
        $session->clear();
        $session->invalidate();

        return new RedirectResponse($this->generateUrl('homepage'));
    }
}
