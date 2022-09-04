<?php

namespace Oka\Notifier\ServerBundle\Model;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
abstract class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var Address
     */
    protected $from;

    /**
     * @var Address
     */
    protected $to;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var \DateTime
     */
    protected $issuedAt;

    public function getId(): string
    {
        return $this->id;
    }

    public function getFrom(): Address
    {
        return $this->from;
    }

    public function setFrom(Address $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): Address
    {
        return $this->to;
    }

    public function setTo(Address $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

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
