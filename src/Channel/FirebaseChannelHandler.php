<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Kreait\Firebase\Messaging;
use Oka\Notifier\Message\Notification;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class FirebaseChannelHandler implements ChannelHandlerInterface
{
    public function __construct(private Messaging $messaging)
    {
    }

    public function supports(Notification $notification): bool
    {
        return in_array(static::getName(), $notification->getChannels(), true);
    }

    public function send(Notification $notification): void
    {
        $receiver = $notification->getReceiver();
        $attributes = $notification->getAttributes();

        $message = Messaging\CloudMessage::new()
            ->withNotification(Messaging\Notification::create(
                $notification->getTitle(),
                $notification->getMessage(),
                $attributes['imageUrl'] ?? null
            ))
            ->withHighestPossiblePriority();

        if (!empty($attributes)) {
            $message->withData($attributes['data']);
        }

        switch ($receiver->getName()) {
            case 'topic':
                $message->toTopic($receiver->getValue());
                break;

            case 'condition':
                $message->toCondition($receiver->getValue());
                break;

            default:
                $message->toToken($receiver->getValue());
                break;
        }

        $this->messaging->send($message);
    }

    public static function getName(): string
    {
        return 'firebase';
    }
}
