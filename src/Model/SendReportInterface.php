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
    
    public function getPaylaod(): array;
    
    public function setPaylaod(array $paylaod): self;
    
    public function getIssuedAt(): \DateTimeInterface;
    
    public function setIssuedAt(\DateTimeInterface $channel): self;
}
