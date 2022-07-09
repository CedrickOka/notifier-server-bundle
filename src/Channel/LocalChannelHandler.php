<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Model\Address;
use Oka\Notifier\ServerBundle\Service\MessageManager;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class LocalChannelHandler implements ChannelHandlerInterface
{
    private $messageManager;

    public function __construct(MessageManager $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    public function supports(Notification $notification): bool
    {
        return in_array(static::getName(), $notification->getChannels(), true);
    }

    public function send(Notification $notification): void
    {
        $this->messageManager->create(
            Address::fromArray($notification->getSender()->toArray()),
            Address::fromArray($notification->getReceiver()->toArray()),
            $notification->getMessage(),
            $notification->getTitle()
        );
    }

    public static function getName(): string
    {
        return 'local';
    }
}
