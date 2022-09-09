<?php

namespace Oka\Notifier\ServerBundle\Model;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
interface ContactInterface
{
    public function getId(): string;

    public function getChannel(): string;

    public function setChannel(string $channel): self;

    public function getName(): string;

    public function setName(string $name): self;

    public function getAddresses(): iterable;

    public function setAddresses(iterable $addresses): self;
}
