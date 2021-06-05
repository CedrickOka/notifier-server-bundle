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
class ClickatellChannelHandler implements SmsChannelHandlerInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;
    
    public function __construct(string $url, string $token, bool $debug)
    {
        $this->httpClient = new Client([
            'base_uri' => $url,
            RequestOptions::DEBUG => $debug,
            RequestOptions::HEADERS => [
                'X-Version' => '1',
                'Accept' => 'application/json',
                'Authorization' => sprintf('Bearer %s', $token)
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
            $response = $this->httpClient->post('/rest/message', [
                RequestOptions::JSON => [
                    'text' => $notification->getMessage(),
                    'to' => [$notification->getReceiver()->getValue()]
                ]
            ]);
        } catch (ClientException $e) {
            throw new InvalidNotificationException(null, null, $e);
        }
    }
    
    public static function getName(): string
    {
        return 'clickatell';
    }
}
