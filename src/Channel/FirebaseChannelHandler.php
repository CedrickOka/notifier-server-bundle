<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Oka\Notifier\Message\Notification;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class FirebaseChannelHandler implements ChannelHandlerInterface
{
    private $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    public function supports(Notification $notification): bool
    {
        return in_array(static::getName(), $notification->getChannels(), true);
    }

    public function send(Notification $notification): void
    {
        $receiver = $notification->getReceiver();
        $attributes = $notification->getAttributes();

        $message = CloudMessage::withTarget('token', $receiver->getValue())
        ->withNotification(\Kreait\Firebase\Messaging\Notification::create(
            $notification->getTitle(),
            $notification->getMessage(),
            $attributes['imageUrl'] ?? null
        ));

        unset($attributes['imageUrl']);

        if (false === empty($attributes)) {
            $message->withData($attributes);
        }

        $this->messaging->send($message);
    }

    public static function getName(): string
    {
        return 'firebase';
    }
}
