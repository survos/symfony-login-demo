<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Attribute;
use App\Entity\AttributeValue;
use App\Entity\PremiumUser;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserAttributeValue;
use App\Entity\UserEmail;
use App\Entity\UserRole;
use App\Security\RoleProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use MsgPhp\Eav\Infrastructure\Uuid\AttributeUuid;
use MsgPhp\Eav\Infrastructure\Uuid\AttributeValueUuid;
use MsgPhp\User\Infrastructure\Uuid\UserUuid;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

final class AppFixtures extends Fixture
{
    private const PASSWORD = 'pass';

    private $passwordEncoder;

    public function __construct(PasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        // roles
        $manager->persist($adminRole = new Role(RoleProvider::ROLE_ADMIN));

        // attributes
        $manager->persist($this->createAttribute(Attribute::GOOGLE_OAUTH_ID));
        $manager->persist($this->createAttribute(Attribute::FACEBOOK_OAUTH_ID));
        $manager->persist($boolAttr = $this->createAttribute());
        $manager->persist($intAttr = $this->createAttribute());
        $manager->persist($floatAttr = $this->createAttribute());
        $manager->persist($stringAttr = $this->createAttribute());
        $manager->persist($dateTimeAttr = $this->createAttribute());

        // users
        $user = $this->createUser('user@domain.dev');
        $user->enable();
        $user->confirm();
        $manager->persist($user);
        $manager->persist(new UserEmail($user, 'other@domain.dev'));
        $manager->persist(new UserEmail($user, 'secondary@domain.dev', true));
        $manager->persist($this->createUserAttributeValue($user, $boolAttr, true));
        $manager->persist($this->createUserAttributeValue($user, $boolAttr, false));
        $manager->persist($this->createUserAttributeValue($user, $boolAttr, null));
        $manager->persist($this->createUserAttributeValue($user, $intAttr, 123));
        $manager->persist($this->createUserAttributeValue($user, $intAttr, -456));
        $manager->persist($this->createUserAttributeValue($user, $floatAttr, 123.0123456789));
        $manager->persist($this->createUserAttributeValue($user, $floatAttr, -0.123));
        $manager->persist($this->createUserAttributeValue($user, $stringAttr, 'text'));
        $manager->persist($this->createUserAttributeValue($user, $dateTimeAttr, new \DateTimeImmutable()));

        $user = $this->createUser('user+disabled@domain.dev');
        $manager->persist($user);

        $user = $this->createUser('user+admin@domain.dev');
        $user->enable();
        $user->confirm();
        $manager->persist($user);
        $manager->persist(new UserRole($user, $adminRole));

        $user = $this->createUser('user+admin+disabled@domain.dev');
        $manager->persist($user);
        $manager->persist(new UserRole($user, $adminRole));

        $premiumUser = $this->createUser('user+premium@domain.dev', true);
        $premiumUser->enable();
        $premiumUser->confirm();
        $manager->persist($premiumUser);

        $manager->flush();
    }

    private function createAttribute($id = null): Attribute
    {
        return new Attribute(AttributeUuid::fromValue($id));
    }

    private function createUser(string $email, bool $premium = false, string $password = self::PASSWORD): User
    {
        $password = $this->passwordEncoder->encodePassword($password, null);

        if ($premium) {
            return new PremiumUser(new UserUuid(), $email, $password);
        }

        return new User(new UserUuid(), $email, $password);
    }

    private function createUserAttributeValue(User $user, Attribute $attribute, $value): UserAttributeValue
    {
        return new UserAttributeValue($user, new AttributeValue(new AttributeValueUuid(), $attribute, $value));
    }
}
