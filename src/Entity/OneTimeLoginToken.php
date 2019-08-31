<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\User\Model\UserField;

/**
 * @ORM\Entity()
 *
 * @final
 */
class OneTimeLoginToken
{
    use UserField;

    /** @ORM\Column() @ORM\Id() */
    private $token;

    /** @ORM\Column(nullable=true) */
    private $redirectUrl;

    public function __construct(User $user, ?string $token = null, ?string $redirectUrl = null)
    {
        $this->user = $user;
        $this->token = $token ?? bin2hex(random_bytes(32));
        $this->redirectUrl = $redirectUrl;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }
}
