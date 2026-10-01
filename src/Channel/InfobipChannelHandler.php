<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Exception\InvalidNotificationException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class InfobipChannelHandler implements SmsChannelHandlerInterface
{
    public function __construct(private HttpClientInterface $httpClient, string $apiKey, bool $debug)
    {
        $this->httpClient = $httpClient->withOptions([
            'base_uri' => 'https://api.infobip.com',
            'headers' => ['Authorization' => sprintf('App %s', $apiKey)],
        ]);
    }

    public function supports(Notification $notification): bool
    {
        return in_array(static::getName(), $notification->getChannels(), true);
    }

    public function send(Notification $notification): void
    {
        $response = $this->httpClient->request(
            'POST',
            '/sms/2/text/advanced',
            [
                'json' => [
                    'messages' => [
                        [
                            'from' => $notification->getSender()->getValue(),
                            'destinations' => [
                                ['to' => $notification->getReceiver()->getValue()],
                            ],
                            'text' => $notification->getMessage(),
                        ],
                    ],
                ],
            ]
        );

        try {
            $response->getContent();
        } catch (ExceptionInterface $e) {
            throw new InvalidNotificationException(null, null, $e);
        }
    }

    public static function getName(): string
    {
        return 'infobip';
    }
}
