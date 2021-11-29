<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Oka\Notifier\Message\Notification;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SmsChannelHandler implements ChannelHandlerInterface
{
    private $handlers;
    private static $name = 'sms';

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
                
                static::$name = $handler::getName();
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

    public static function getName(): string
    {
        return static::$name;
    }
}
