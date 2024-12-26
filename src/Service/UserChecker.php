<?php

namespace App\Service;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (method_exists($user, 'getBannedAt') && $user->getBannedAt() !== null) {
            throw new CustomUserMessageAccountStatusException(
                'Your account has been banned since ' . $user->getBannedAt()->format('Y-m-d H:i') . '.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {}
}
