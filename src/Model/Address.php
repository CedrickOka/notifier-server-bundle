<?php

namespace Oka\Notifier\ServerBundle\Model;

use Oka\Notifier\Message\Address as BaseAddress;
use Oka\Notifier\Message\Enum\AddressType;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class Address extends BaseAddress
{
    public function setType(AddressType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function equals(Address $object): bool
    {
        return $object->getName() === $this->name && $object->getValue() === $this->value;
    }

    public function __serialize(): array
    {
        return [
            'value' => $this->value,
            'name' => $this->name,
            'type' => $this->type->value,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->value = $data['value'];
        $this->name = $data['name'];
        $this->type = AddressType::from($data['type']);
    }
}
