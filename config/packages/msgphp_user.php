<?php

declare(strict_types=1);

use App\Security\RoleProvider;
use MsgPhp\User\Role;
use MsgPhp\User\Role\UserRoleProvider;
use MsgPhp\User\User;
use MsgPhp\User\UserAttributeValue;
use MsgPhp\User\UserEmail;
use MsgPhp\User\Username;
use MsgPhp\User\UserRole;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('msgphp_user', [
        'class_mapping' => [
            Role::class => \App\Entity\Role::class,
            User::class => \App\Entity\User::class,
            UserAttributeValue::class => \App\Entity\UserAttributeValue::class,
            UserEmail::class => \App\Entity\UserEmail::class,
            Username::class => \App\Entity\Username::class,
            UserRole::class => \App\Entity\UserRole::class,
        ],
        'default_id_type' => 'uuid',
        'username_lookup' => [
            ['target' => \App\Entity\UserEmail::class, 'field' => 'email', 'mapped_by' => 'user'],
        ],
        'role_providers' => [
            'default' => [RoleProvider::ROLE_USER],
            UserRoleProvider::class,
            RoleProvider::class,
        ],
    ]);
};
