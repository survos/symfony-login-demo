<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Eav\AttributeValue as BaseAttributeValue;
use MsgPhp\Eav\AttributeValueId;

/**
 * @ORM\Entity()
 *
 * @final
 */
class AttributeValue extends BaseAttributeValue
{
    /** @ORM\Id @ORM\Column(type="msgphp_attribute_value_id", length=191) */
    private $id;

    public function __construct(AttributeValueId $id, Attribute $attribute, $value)
    {
        parent::__construct($attribute, $value);

        $this->id = $id;
    }

    public function getId(): AttributeValueId
    {
        return $this->id;
    }
}
