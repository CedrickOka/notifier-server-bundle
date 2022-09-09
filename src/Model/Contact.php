<?php

namespace Oka\Notifier\ServerBundle\Model;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
abstract class Contact implements ContactInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var iterable
     */
    protected $addresses;

    public function __construct()
    {
        $this->addresses = [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddresses(): iterable
    {
        return array_map(function ($data) {
            return Address::fromArray($data);
        }, $this->addresses);
    }

    public function setAddresses(iterable $addresses): self
    {
        $this->addresses = [];

        foreach ($addresses as $address) {
            $this->addAddress($address instanceof Address ? $address : Address::fromArray($address));
        }

        return $this;
    }

    public function addAddress(Address $address): self
    {
        foreach ($this->addresses as $item) {
            if (true === $address->equals(Address::fromArray($item))) {
                return $this;
            }
        }

        $this->addresses[] = $address->toArray();

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        foreach ($this->addresses as $key => $item) {
            if (false === $address->equals(Address::fromArray($item))) {
                continue;
            }

            unset($this->addresses[$key]);
            $this->addresses = array_values($this->addresses);
            break;
        }

        return $this;
    }
}
