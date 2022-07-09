<?php

namespace Oka\Notifier\ServerBundle\Channel;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Exception\InvalidNotificationException;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class WirepickChannelHandler implements SmsChannelHandlerInterface
{
    private $username;
    private $password;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $httpClient;

    public function __construct(string $username, string $password, bool $debug)
    {
        $this->username = $username;
        $this->password = $password;
        $this->httpClient = new Client([
            'base_uri' => 'https://api.wirepick.com',
            RequestOptions::DEBUG => $debug,
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
            $response = $this->httpClient->post('/httpsms/send', [
                RequestOptions::QUERY => [
                    'client' => $this->username,
                    'password' => $this->password,
                    'from' => $notification->getSender()->getValue(),
                    'phone' => $notification->getReceiver()->getValue(),
                    'text' => $notification->getMessage(),
                ],
            ]);
        } catch (ClientException $e) {
            throw new InvalidNotificationException(null, null, $e);
        }
    }

    public static function getName(): string
    {
        return 'wirepick';
    }
}
