<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Oka\Notifier\Message\Notification;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SmsChannelHandler implements ChannelHandlerInterface
{
    private $handlers;
    private $delegateHandlerName;

    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    public function supports(Notification $notification): bool
    {
        return in_array(static::getName(), $notification->getChannels(), true);
    }

    public function send(Notification $notification): void
    {
        $lastError = null;

        /** @var \Oka\Notifier\ServerBundle\Channel\ChannelHandlerInterface $handler */
        foreach ($this->handlers as $handler) {
            try {
                $handler->send($notification);
                $this->delegateHandlerName = $handler::getName();

                $lastError = null;
                break;
            } catch (\Exception $e) {
                $lastError = $e;
            }
        }

        if (null !== $lastError) {
            throw $lastError;
        }
    }

    /**
     * Gets the delegate channel handler name.
     */
    public function getDelegateHandlerName(): ?string
    {
        return $this->delegateHandlerName;
    }

    public static function getName(): string
    {
        return 'sms';
    }
}
