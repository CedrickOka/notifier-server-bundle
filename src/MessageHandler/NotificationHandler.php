<?php

namespace Oka\Notifier\ServerBundle\MessageHandler;

use Oka\Notifier\Message\Address;
use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Channel\SmsChannelHandler;
use Oka\Notifier\ServerBundle\Exception\InvalidNotificationException;
use Oka\Notifier\ServerBundle\Service\ContactManager;
use Oka\Notifier\ServerBundle\Service\SendReportManager;
use Psr\Log\LoggerInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class NotificationHandler
{
    public function __construct(
        private iterable $handlers,
        private ?ContactManager $contactManager = null,
        private ?SendReportManager $reportManager = null,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function __invoke(Notification $notification): void
    {
        $noHandlerSelected = true;

        /** @var \Oka\Notifier\ServerBundle\Channel\ChannelHandlerInterface $handler */
        foreach ($this->handlers as $handler) {
            if (false === $handler->supports($notification)) {
                continue;
            }

            if (null !== $this->contactManager && Address::TYPE_CONTACT === $notification->getReceiver()->getType()) {
                $contacts = $this->contactManager->findBy(['channel' => $notification->getReceiver()->getName(), 'name' => $notification->getReceiver()->getValue()]);
            }

            try {
                $handler->send($notification);

                if (null !== $this->logger) {
                    $this->logger->info(
                        sprintf('Notification has been sended on channel "%s" to receiver "%s".', $handler::getName(), (string) $notification->getReceiver()),
                        $this->createLogContext($notification)
                    );
                }

                $sended = true;
                $noHandlerSelected = false;
            } catch (InvalidNotificationException $e) {
                if (null !== $this->logger) {
                    $e = $e->getPrevious() ?? $e;
                    $this->logger->error(
                        sprintf('%s: %s (uncaught exception) at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
                        $this->createLogContext($notification)
                    );
                }

                $sended = false;
            }

            if (null !== $this->reportManager && true === $sended) {
                $payload = $notification->toArray();
                unset($payload['channels'], $payload['message']);

                $this->reportManager->create(
                    $handler instanceof SmsChannelHandler ? $handler->getDelegateHandlerName() ?? $handler->getName() : $handler->getName(),
                    $payload
                );
            }

            $notification->removeChannel($handler->getName());
        }

        if (true === $noHandlerSelected && null !== $this->logger) {
            $this->logger->warning('No handler was able to send this notification.', $this->createLogContext($notification));
        }
    }

    protected function createLogContext(Notification $notification): array
    {
        return [
            'channels' => $notification->getChannels(),
            'attributes' => $notification->getAttributes(),
            'sender' => (string) $notification->getSender(),
            'receiver' => (string) $notification->getReceiver(),
        ];
    }
}
