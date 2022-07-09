<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Oka\Notifier\Message\Notification;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
interface ChannelHandlerInterface
{
    /**
     * Checks if channel handler supports notification.
     */
    public function supports(Notification $notification): bool;

    /**
     * Send notification.
     */
    public function send(Notification $notification): void;

    /**
     * Gets the channel handler name.
     */
    public static function getName(): string;
}
