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
class WirepickChannelHandler implements SmsChannelHandlerInterface
{
    private $clientId;
    private $password;
    
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $httpClient;

    public function __construct(string $clientId, string $password, bool $debug)
    {
        $this->clientId = $clientId;
        $this->password = $password;
        $this->httpClient = new Client([
            'base_uri' => $url,
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
                    'client' => $this->clientId,
                    'password' => $this->password,
                    'from' => $notification->getSender()->getValue(),
                    'phone' => $notification->getReceiver()->getValue(),
                    'text' => $notification->getMessage(),
                ]
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
