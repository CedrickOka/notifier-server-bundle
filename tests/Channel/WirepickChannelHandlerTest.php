<?php

namespace Oka\Notifier\ServerBundle\Tests\Channel;

use Oka\Notifier\Message\Address;
use Oka\Notifier\Message\Notification;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class WirepickChannelHandlerTest extends KernelTestCase
{
    /**
     * @var \Oka\Notifier\ServerBundle\Channel\WirepickChannelHandler
     */
    private $handler;

    public function setUp(): void
    {
        static::bootKernel();
        $this->handler = static::$container->get('oka_notifier_server.channel.wirepick_handler');
    }

    /**
     * @covers
     */
    public function testThatHandlerSupportsChannel()
    {
        $this->assertEquals(true, $this->handler->supports(new Notification(['sms', 'wirepick'], Address::create('test'), Address::create('test'), 'Hello World!')));
        $this->assertEquals(false, $this->handler->supports(new Notification(['sms'], Address::create('test'), Address::create('test'), 'Hello World!')));

        $this->handler->send(new Notification(['wirepick'], Address::create(getenv('SENDER_ADDRESS')), Address::create(getenv('RECEIVER_ADDRESS')), 'Hello World!'));
    }
}
