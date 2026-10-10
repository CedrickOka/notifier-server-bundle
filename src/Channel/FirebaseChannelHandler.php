<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as CloudNotification;
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

        $message = CloudMessage::new()
            ->withNotification(CloudNotification::create(
                $notification->getTitle(),
                $notification->getMessage(),
                $attributes['imageUrl'] ?? null
            ))
            ->withHighestPossiblePriority();

        if (true === $notification->hasAttribute('data')) {
            $message = $message->withData($attributes['data']);
        }

        switch ($receiver->getName()) {
            case 'topic':
                $message = $message->toTopic($receiver->getValue());
                break;

            case 'condition':
                $message = $message->toCondition($receiver->getValue());
                break;

            default:
                $message = $message->toToken($receiver->getValue());
                break;
        }

        $this->messaging->send($message);
    }

    public static function getName(): string
    {
        return 'firebase';
    }
}
