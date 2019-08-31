<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 *
 * @final
 */
class UserInvitation
{
    /** @ORM\Column() @ORM\Id() */
    private $token;

    /** @ORM\Column(unique=true) */
    private $email;

    public function __construct(string $email, ?string $token = null)
    {
        $this->token = $token ?? bin2hex(random_bytes(32));
        $this->email = $email;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
