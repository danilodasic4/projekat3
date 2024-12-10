<?php

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\Repository\ResetPasswordRequestRepositoryTrait;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

/**
 * @extends ServiceEntityRepository<ResetPasswordRequest>
 */
class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    use ResetPasswordRequestRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    /**
     * @param User $user
     */
    public function createResetPasswordRequest(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken): ResetPasswordRequestInterface
    {
        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }

    /**
     * Find a reset password request by token.
     */
    public function findOneByToken(string $token): ?ResetPasswordRequest
    {
        return $this->findOneBy(['hashedToken' => $token]);
    }

    /**
     * Save the reset password request (insert or update).
     */
    public function save(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->_em->persist($resetPasswordRequest);  // Persists the entity to the database
        $this->_em->flush();  // Applies the changes
    }

    /**
     * Remove the reset password request.
     */
    public function remove(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->_em->remove($resetPasswordRequest);  // Removes the entity
        $this->_em->flush();  // Applies the changes
    }
}
