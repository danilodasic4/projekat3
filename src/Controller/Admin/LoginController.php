<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;

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

        $lock = $this->lockFactory->createLock(self::ADMIN_LOCK_KEY, 3600);

        if (!$lock->acquire(false)) {
            return $this->render('admin/login.html.twig', [
                'last_username' => $authenticationUtils->getLastUsername(),
                'error' => 'Another admin is already logged in.',
            ]);
        }
        return $this->render('admin/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/admin/logout', name: 'admin_logout', methods: ['GET'])]
    public function logout(SessionInterface $session): RedirectResponse
    {
        $lock = $this->lockFactory->createLock(self::ADMIN_LOCK_KEY);
        if ($lock->isAcquired()) {
            $lock->release();
        }

        $session->clear();
        $session->invalidate();

        return new RedirectResponse($this->generateUrl('homepage'));
    }
}
