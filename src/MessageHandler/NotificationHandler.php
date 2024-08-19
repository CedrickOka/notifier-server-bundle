<?php

namespace Oka\Notifier\ServerBundle\MessageHandler;

use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Channel\SmsChannelHandler;
use Oka\Notifier\ServerBundle\Exception\InvalidNotificationException;
use Oka\Notifier\ServerBundle\Service\SendReportManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class NotificationHandler implements MessageHandlerInterface
{
    private $handlers;
    private $logger;
    private $reportManager;

    public function __construct(iterable $handlers, ?SendReportManager $reportManager = null, ?LoggerInterface $logger = null)
    {
        $this->handlers = $handlers;
        $this->reportManager = $reportManager;
        $this->logger = $logger;
    }

    public function __invoke(Notification $notification): void
    {
        $noHandlerSelected = true;

        /** @var \Oka\Notifier\ServerBundle\Channel\ChannelHandlerInterface $handler */
        foreach ($this->handlers as $handler) {
            if (false === $handler->supports($notification)) {
                continue;
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

            if (true === $sended && null !== $this->reportManager) {
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
