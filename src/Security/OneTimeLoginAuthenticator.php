<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\OneTimeLoginToken;
use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\User\Infrastructure\Security\UserIdentity;
use MsgPhp\User\Infrastructure\Security\UserIdentityProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

final class OneTimeLoginAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $userIdentityProvider;
    private $urlGenerator;

    public function __construct(EntityManagerInterface $em, UserIdentityProvider $userIdentityProvider, UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->userIdentityProvider = $userIdentityProvider;
        $this->urlGenerator = $urlGenerator;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('login'));
    }

    public function supports(Request $request): bool
    {
        return 'login' === $request->attributes->get('_route')
            && $request->isMethod(Request::METHOD_GET)
            && $request->query->has('token');
    }

    public function getCredentials(Request $request)
    {
        return $request->query->get('token');
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        if (null === $oneTimeLoginToken = $this->getOneTimeLoginToken($credentials)) {
            return null;
        }

        return $this->userIdentityProvider->fromUser($oneTimeLoginToken->getUser());
    }

    public function checkCredentials($credentials, UserInterface $identity): bool
    {
        if (!$identity instanceof UserIdentity) {
            return false;
        }

        if (null === $oneTimeLoginToken = $this->getOneTimeLoginToken($credentials)) {
            return false;
        }

        return $oneTimeLoginToken->getUserId()->equals($identity->getUserId());
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        if (null === $oneTimeLoginToken = $this->getOneTimeLoginTokenOnce($this->getCredentials($request))) {
            return null;
        }

        return new RedirectResponse($oneTimeLoginToken->getRedirectUrl() ?? $this->urlGenerator->generate('profile'));
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

    private function getOneTimeLoginToken(string $token): ?OneTimeLoginToken
    {
        return $this->em->find(OneTimeLoginToken::class, $token);
    }

    private function getOneTimeLoginTokenOnce(string $token): ?OneTimeLoginToken
    {
        if (null !== $token = $this->getOneTimeLoginToken($token)) {
            $this->em->remove($token);
            $this->em->flush();
        }

        return $token;
    }
}
