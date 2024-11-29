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
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === User::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
{
    // Uzima user_id iz rute
    $userId = (int) $request->get('user_id');
    
    // Pronađi korisnika u bazi
    $user = $this->userRepository->find($userId);

    // Ako korisnik ne postoji, baca se izuzetak
    if (!$user) {
        throw new AccessDeniedException('User not found.');
    }

    // Vraća korisnika kao vrednost koja će biti prosleđena u kontroler
    yield $user;
}

}
