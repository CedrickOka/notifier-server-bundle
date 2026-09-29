<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Exception\InvalidNotificationException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class ClickatellChannelHandler implements SmsChannelHandlerInterface
{
    public function __construct(private HttpClientInterface $httpClient, string $token, bool $debug)
    {
        $this->httpClient = $httpClient->withOptions([
            'base_uri' => 'https://api.clickatell.com',
            'auth_bearer' => $token,
            'headers' => ['X-Version' => '1'],
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
            '/rest/message',
            [
                'json' => [
                    'text' => $notification->getMessage(),
                    'from' => $notification->getSender()->getValue(),
                    'to' => [$notification->getReceiver()->getValue()],
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
        return 'clickatell';
    }
}
