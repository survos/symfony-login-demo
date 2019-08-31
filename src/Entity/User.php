<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;
use MsgPhp\Domain\Model\CanBeConfirmed;
use MsgPhp\Domain\Model\CanBeEnabled;
use MsgPhp\Domain\Model\CreatedAtField;
use MsgPhp\User\Credential\EmailPassword;
use MsgPhp\User\Model\AttributeValuesField;
use MsgPhp\User\Model\EmailPasswordCredential;
use MsgPhp\User\Model\EmailsField;
use MsgPhp\User\Model\ResettablePassword;
use MsgPhp\User\Model\RolesField;
use MsgPhp\User\User as BaseUser;
use MsgPhp\User\UserId;

/**
 * @ORM\Entity()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string")
 * @ORM\DiscriminatorMap({"user" = "User", "premium_user" = "PremiumUser"})
 */
class User extends BaseUser implements DomainEventHandler
{
    use CreatedAtField;
    use EmailPasswordCredential;
    use ResettablePassword;
    use CanBeEnabled;
    use CanBeConfirmed;
    use EmailsField;
    use RolesField;
    use AttributeValuesField;
    use DomainEventHandlerTrait;

    /** @ORM\Id() @ORM\Column(type="msgphp_user_id", length=191) */
    private $id;

    public function __construct(UserId $id, string $email, string $password)
    {
        $this->id = $id;
        $this->createdAt = new \DateTimeImmutable();
        $this->credential = new EmailPassword($email, $password);
        $this->confirmationToken = bin2hex(random_bytes(32));
    }

    public function getId(): UserId
    {
        return $this->id;
    }
}
