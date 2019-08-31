<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\PremiumUser;
use App\Entity\User;
use MsgPhp\User\Role\RoleProvider as BaseRoleProvider;
use MsgPhp\User\User as BaseUser;

final class RoleProvider implements BaseRoleProvider
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_PREMIUM_USER = 'ROLE_PREMIUM_USER';
    public const ROLE_ENABLED_USER = 'ROLE_ENABLED_USER';
    public const ROLE_DISABLED_USER = 'ROLE_DISABLED_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @param User $user
     */
    public function getRoles(BaseUser $user): array
    {
        $roles = $user->isEnabled() ? [self::ROLE_ENABLED_USER] : [self::ROLE_DISABLED_USER];

        if ($user instanceof PremiumUser) {
            $roles[] = self::ROLE_PREMIUM_USER;
        }

        return $roles;
    }
}
