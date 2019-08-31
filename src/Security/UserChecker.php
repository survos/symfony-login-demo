<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\User\Infrastructure\Security\UserIdentity;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $em, ?LoggerInterface $logger = null)
    {
        $this->em = $em;
        $this->logger = $logger ?? new NullLogger();
    }

    public function checkPreAuth(UserInterface $identity): void
    {
        if (!$identity instanceof UserIdentity) {
            return;
        }

        if (null === $user = $this->em->find(User::class, $userId = $identity->getUserId())) {
            throw new AuthenticationCredentialsNotFoundException('Bad credentials.');
        }

        /** @var User $user */
        if (!$user->isEnabled()) {
            $this->logger->info('Disabled user login attempt.', ['id' => $userId->toString(), 'email' => $user->getEmail()]);

            throw new DisabledException('Bad credentials.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
