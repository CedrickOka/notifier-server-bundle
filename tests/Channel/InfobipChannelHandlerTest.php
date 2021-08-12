<?php

namespace Oka\Notifier\ServerBundle\Tests\Channel;

use Oka\Notifier\Message\Address;
use Oka\Notifier\Message\Notification;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use GuzzleHttp\Client;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class InfobipChannelHandlerTest extends KernelTestCase
{
    /**
     * @var \Oka\Notifier\ServerBundle\Channel\ClickatellChannelHandler
     */
    private $handler;

    public function setUp(): void
    {
        static::bootKernel();
        $this->handler = static::$container->get('oka_notifier_server.channel.infobip_handler');
    }

    /**
     * @covers
     */
    public function testThatHandlerSupportsChannel()
    {
        $this->assertEquals(true, $this->handler->supports(new Notification(['sms', 'infobip'], Address::create('test'), Address::create('test'), 'Hello World!')));
        $this->assertEquals(false, $this->handler->supports(new Notification(['sms'], Address::create('test'), Address::create('test'), 'Hello World!')));
        
        $reflObject = new \ReflectionObject($this->handler);
        $reflProperty= $reflObject->getProperty('httpClient');
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($this->handler, $this->createMock(Client::class));

        $this->handler->send(new Notification(['sms', 'infobip'], Address::create('test'), Address::create('2250554020558'), 'Hello World!'));
    }
}
