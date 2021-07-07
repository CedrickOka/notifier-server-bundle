<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Exception\InvalidNotificationAddressException;
use Oka\Notifier\ServerBundle\Exception\InvalidNotificationException;
use gateway\protocol\GsmEncoder;
use gateway\protocol\SmppClient;
use gateway\protocol\SmppException;
use gateway\transport\TSocket;
use gateway\transport\TTransportException;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SmppChannelHandler implements SmsChannelHandlerInterface
{
    /**
     * @var array
     */
    private $configuration;

    public function __construct(string $dsn, bool $debug)
    {
        if (false === $parsedUrl = parse_url($dsn)) {
            throw new \InvalidArgumentException(sprintf('The given SMS Channel DSN "%s" is invalid.', $dsn));
        }

        $parsedQuery = [];
        parse_str($parsedUrl['query'] ?? '', $parsedQuery);
        $this->configuration = array_replace_recursive([
            'host' => $parsedUrl['host'] ?? 'localhost',
            'port' => $parsedUrl['port'] ?? 8775,
            'systemId' => $parsedUrl['user'] ?? 'guest',
            'password' => $parsedUrl['pass'] ?? 'guest',
            'debug' => $debug,
            'receive_timeout' => 10000,
            'send_timeout' => 10000,
        ], $parsedQuery);
    }

    public function supports(Notification $notification): bool
    {
        return in_array(static::getName(), $notification->getChannels(), true);
    }

    public function send(Notification $notification): void
    {
        // Construct transport and client
        $transport = new TSocket($this->configuration['host'], $this->configuration['port']);
        $transport->setDebug($this->configuration['debug']);
        $transport->setSendTimeout($this->configuration['send_timeout']);
        $transport->setRecvTimeout($this->configuration['receive_timeout']);

        $smppClient = new SmppClient($transport);
        $smppClient->debug = $this->configuration['debug'];

        // Set static options for SMPP client.
        SmppClient::$system_type = '';
        SmppClient::$sms_esm_class = 0x01;
        SmppClient::$sms_service_type = '';
        SmppClient::$sms_registered_delivery_flag = 0x01;
        SmppClient::$sms_use_msg_payload_for_csms = true;
        SmppClient::$sms_null_terminate_octetstrings = false;

        // Establish connection with SDP server
        $transport->open();
        $smppClient->bindTransmitter($this->configuration['systemId'], $this->configuration['password']);
        $this->logInfo(sprintf('Channel SMS: Connection  established with the SDP on address "%s:%s".', $this->configuration['host'], $this->configuration['port']), $notification);

        $sender = $notification->getSender();
        $receiver = $notification->getReceiver();
        $attributes = $notification->getAttributes();

        if (false === ctype_digit($receiver->getValue())) {
            throw new InvalidNotificationAddressException(sprintf('Cannot send SMS to receiver "%s".', $receiver->getValue()));
        }

        // Prepare message
        $dataCoding = $attributes['dataCoding'] ?? \SMPP\DATA_CODING_DEFAULT;

        if (\SMPP\DATA_CODING_DEFAULT === $dataCoding) {
            $encodedMessage = GsmEncoder::utf8_to_gsm0338($notification->getMessage());
            $encodedSender = GsmEncoder::utf8_to_gsm0338((string) $sender);
        } else {
            $encodedMessage = $notification->getMessage();
            $encodedSender = (string) $sender;
        }

        // Contruct SMPP Address from
        try {
            if (false === ctype_digit($sender->getValue())) {
                $from = new \SMPP\Address($encodedSender, \SMPP\TON_ALPHANUMERIC);
            } elseif (10000 > $sender->getValue()) {
                $from = new \SMPP\Address($sender->getValue(), \SMPP\TON_NATIONAL, \SMPP\NPI_E164);
            } else {
                $from = new \SMPP\Address($sender->getValue(), \SMPP\TON_INTERNATIONAL, \SMPP\NPI_E164);
            }

            $to = new \SMPP\Address($receiver->getValue(), \SMPP\TON_INTERNATIONAL, \SMPP\NPI_E164);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidNotificationAddressException(null, null, $e);
        } finally {
            // Cleanup
            $smppClient->close();
            unset($smppClient);
        }

        try {
            $smppClient->sendSMS($from, $to, $encodedMessage, null, $dataCoding);
        } catch (\Exception $e) {
            if (!$e instanceof TTransportException && !$e instanceof SmppException) {
                throw new InvalidNotificationException(null, null, $e);
            }

            throw $e;
        } finally {
            // Cleanup
            $smppClient->close();
            unset($smppClient);
        }
    }

    public static function getName(): string
    {
        return 'smpp';
    }
}
