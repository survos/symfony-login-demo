<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;
use MsgPhp\Domain\Model\CanBeConfirmed;
use MsgPhp\User\User;
use MsgPhp\User\UserEmail as BaseUserEmail;

/**
 * @ORM\Entity()
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="emails")
 * })
 *
 * @final
 */
class UserEmail extends BaseUserEmail implements DomainEventHandler
{
    use CanBeConfirmed;
    use DomainEventHandlerTrait;

    public function __construct(User $user, string $email, bool $confirm = false)
    {
        parent::__construct($user, $email);

        if ($confirm) {
            $this->confirm();
        } else {
            $this->confirmationToken = bin2hex(random_bytes(32));
        }
    }
}
