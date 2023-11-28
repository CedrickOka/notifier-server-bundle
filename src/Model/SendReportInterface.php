<?php

namespace Oka\Notifier\ServerBundle\Model;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
interface SendReportInterface
{
    public function getId(): string;

    public function getChannel(): string;

    public function setChannel(string $channel): self;

    public function getPayload(): array;

    public function setPayload(array $payload): self;

    public function getIssuedAt(): \DateTimeInterface;

    public function setIssuedAt(\DateTimeInterface $issuedAt): self;
}
