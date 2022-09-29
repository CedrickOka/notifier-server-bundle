<?php

namespace Oka\Notifier\ServerBundle\Model;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
interface MessageInterface
{
    public function getId(): string;

    public function getFrom(): Address;

    public function setFrom(Address $channel): self;

    public function getTo(): Address;

    public function setTo(Address $channel): self;

    public function getBody(): string;

    public function setBody(string $body): self;

    public function getSubject(): ?string;

    public function setSubject(string $subject): self;

    public function getOwnerId(): ?string;

    public function setOwnerId(string $ownerId): self;

    public function getIssuedAt(): \DateTimeInterface;

    public function setIssuedAt(\DateTimeInterface $issuedAt): self;
}
