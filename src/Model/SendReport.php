<?php

namespace Oka\Notifier\ServerBundle\Model;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
abstract class SendReport implements SendReportInterface
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
     * @var array
     */
    protected $payload;

    /**
     * @var \DateTime
     */
    protected $issuedAt;

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

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getIssuedAt(): \DateTimeInterface
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(\DateTimeInterface $issuedAt): self
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    public function prePersist()
    {
        if (null === $this->issuedAt) {
            $this->issuedAt = new \DateTime();
        }
    }
}
