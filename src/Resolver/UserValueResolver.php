<?php

namespace App\Resolver;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class UserValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(private readonly UserRepository $userRepository)
    {}

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === User::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable

    {
        $userId = (int) $request->get('user_id');
        
        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new AccessDeniedException('User not found.');
        }

        yield $user;
    }

}