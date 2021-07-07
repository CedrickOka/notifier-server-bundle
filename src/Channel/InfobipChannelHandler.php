<?php

namespace Oka\Notifier\ServerBundle\Channel;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\ClientException;
use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Exception\InvalidNotificationException;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class InfobipChannelHandler implements SmsChannelHandlerInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    public function __construct(string $url, string $apiKey, bool $debug)
    {
        $this->httpClient = new Client([
            'base_uri' => $url,
            RequestOptions::DEBUG => $debug,
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => sprintf('App %s', $apiKey)
            ]
        ]);
    }

    public function supports(Notification $notification): bool
    {
        return in_array(static::getName(), $notification->getChannels(), true);
    }

    public function send(Notification $notification): void
    {
        try {
            /** @var \Psr\Http\Message\ResponseInterface $response */
            $response = $this->httpClient->post('/sms/2/text/advanced', [
                RequestOptions::JSON => [
                    'messages' => [
                        'from' => $notification->getSender()->getValue(),
                        'destinations' => [
                            'to' => $notification->getReceiver()->getValue()
                       ],
                       'text' => $notification->getMessage()
                    ]
                ]
            ]);
        } catch (ClientException $e) {
            throw new InvalidNotificationException(null, null, $e);
        }
    }

    public static function getName(): string
    {
        return 'infobip';
    }
}
