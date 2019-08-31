<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\User\UserAttributeValue as BaseUserAttributeValue;

/**
 * @ORM\Entity()
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="attributeValues")
 * })
 *
 * @final
 */
class UserAttributeValue extends BaseUserAttributeValue
{
}
