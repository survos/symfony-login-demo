<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Attribute;
use App\Entity\UserAttributeValue;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use MsgPhp\Domain\Exception\EntityNotFound;
use MsgPhp\Eav\Infrastructure\Uuid\AttributeUuid;
use MsgPhp\User\Command\AddUserAttributeValue;
use MsgPhp\User\Command\ConfirmUser;
use MsgPhp\User\Command\CreateUser;
use MsgPhp\User\Infrastructure\Security\UserIdentityProvider;
use MsgPhp\User\Infrastructure\Uuid\UserUuid;
use MsgPhp\User\Repository\UserAttributeValueRepository;
use MsgPhp\User\Repository\UserRepository;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

final class OauthUserProvider implements OAuthAwareUserProviderInterface
{
    private $userRepository;
    private $userAttributeValueRepository;
    private $userIdentityProvider;
    private $bus;

    public function __construct(UserRepository $userRepository, UserAttributeValueRepository $userAttributeValueRepository, UserIdentityProvider $userIdentityProvider, MessageBusInterface $bus)
    {
        $this->userRepository = $userRepository;
        $this->userAttributeValueRepository = $userAttributeValueRepository;
        $this->userIdentityProvider = $userIdentityProvider;
        $this->bus = $bus;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $owner = $response->getResourceOwner()->getName();
        $username = $response->getUsername();

        if (!\defined($const = Attribute::class.'::'.strtoupper($owner).'_OAUTH_ID')) {
            throw new \LogicException(sprintf('Missing constant "%s" for OAuth resoure owner "%s"', $const, $owner));
        }

        $attributeId = AttributeUuid::fromValue(\constant($const));
        $userAttributeValues = $this->userAttributeValueRepository->findAllByAttributeIdAndValue($attributeId, $username);

        if ($userAttributeValues->isEmpty()) {
            if (null === $email = $response->getEmail()) {
                throw new CustomUserMessageAuthenticationException(sprintf('Oauth resource owner "%s" requires e-mail availability and appropriate read-privilege.', $owner));
            }

            try {
                $user = $this->userRepository->findByUsername($email);
                $userId = $user->getId();
            } catch (EntityNotFound $e) {
                $userId = new UserUuid();
                // @todo validate username/email availability
                $this->bus->dispatch(new CreateUser([
                    'id' => $userId,
                    'email' => $email,
                    'password' => bin2hex(random_bytes(32)),
                ]));
                $this->bus->dispatch(new ConfirmUser($userId));

                $user = $this->userRepository->find($userId);
            }

            $this->bus->dispatch(new AddUserAttributeValue($userId, $attributeId, $username));
        } else {
            /** @var UserAttributeValue $userAttributeValue */
            $userAttributeValue = $userAttributeValues->first();
            $user = $userAttributeValue->getUser();
        }

        return $this->userIdentityProvider->fromUser($user);
    }
}
