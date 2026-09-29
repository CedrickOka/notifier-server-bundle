<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Exception\InvalidNotificationException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class WirepickChannelHandler implements SmsChannelHandlerInterface
{
    public function __construct(private HttpClientInterface $httpClient, string $username, string $password, bool $debug)
    {
        $this->httpClient = $httpClient->withOptions([
            'base_uri' => 'https://api.wirepick.com',
            'query' => [
                'client' => $username,
                'password' => $password,
            ],
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
            '/httpsms/send',
            [
                'query' => [
                    'from' => $notification->getSender()->getValue(),
                    'phone' => $notification->getReceiver()->getValue(),
                    'text' => $notification->getMessage(),
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
        return 'wirepick';
    }
}
