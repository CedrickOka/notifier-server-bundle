<?php

namespace Oka\Notifier\ServerBundle\Tests\Controller;

use Oka\Notifier\Message\Address;
use Oka\Notifier\Message\Notification;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class NotificationControllerTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    public function setUp(): void
    {
        $message = new Notification(['sms'], new Address('MTN DRIVE'), new Address('22554020558'), 'Hello World!', null);
        $service = $this->createMock(MessageBusInterface::class);
        $service->method('dispatch')->willReturn(new \Symfony\Component\Messenger\Envelope($message));

        $this->client = static::createClient();
        static::$container->set('message_bus', $service);
        static::$container->set('messenger.default_bus', $service);
    }

    /**
     * @covers
     */
    public function testCanSendNotificatonOnSMSChannel()
    {
        $this->client->request('POST', '/v1/rest/notifications', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], '{"notifications": [{"channels": ["sms"], "sender": "MTN DRIVE", "receiver": "22554020558", "message": "Hello World!"}]}');

        $this->assertResponseStatusCodeSame(204);
    }

    /**
     * @covers
     * @depends testCanSendNotificatonOnSMSChannel
     */
    public function testCannotSendNotificatonWithAWrongReceiver()
    {
        $this->client->request('POST', '/v1/rest/notifications', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], '{"notifications": [{"channels": ["sms"], "sender": "MTN DRIVE", "receiver": {"name": "22554020558"}, "message": "Hello World!"}]}');

        $this->assertResponseStatusCodeSame(400);
    }
}
