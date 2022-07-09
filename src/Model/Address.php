<?php

namespace Oka\Notifier\ServerBundle\Model;

use Oka\Notifier\Message\Address as BaseAddress;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class Address extends BaseAddress
{
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

    public static function fromArray(array $data): self
    {
        $self = new self($data['value'], $data['name'] ?? null);

        return $self;
    }
}
