<?php
namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CachingService;

class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CachingService $cachingService,
    ) {}

    #[Route('/admin/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findAll(),
            'loggedInUsersCount' => $this->cachingService->getLoggedInUsersCount(),
        ]);
    }


    #[Route('/admin/users/{user_id}/ban', name: 'admin_ban_user', methods: ['POST'])]
    public function banUser(
        #[ValueResolver(UserValueResolver::class)] User $user,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if ($user->getBannedAt() !== null) {
            return new JsonResponse(['message' => 'User is already banned'], 400);
        }
    
        $user->setBannedAt(new \DateTime());
    
        $entityManager->persist($user);
        $entityManager->flush();
    
        return new JsonResponse(['message' => 'User successfully banned'], 200);
    }
    
    #[Route('/admin/users/{user_id}/unban', name: 'admin_unban_user', methods: ['POST'])]
    public function unbanUser(
        #[ValueResolver(UserValueResolver::class)] User $user, 
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        if ($user->getBannedAt() === null) {
            return new Response('User is not banned', 400);
        }

        $user->setBannedAt(null);

        $entityManager->persist($user);
        $entityManager->flush();

        $previousUrl = $request->headers->get('referer');
        return $this->redirect($previousUrl);
    }   
}