<?php

namespace Oka\Notifier\ServerBundle\Tests\Channel;

use Oka\Notifier\Message\Address;
use Oka\Notifier\Message\Notification;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class ClickatellChannelHandlerTest extends KernelTestCase
{
    /**
     * @var \Oka\Notifier\ServerBundle\Channel\ClickatellChannelHandler
     */
    private $handler;

    public function setUp(): void
    {
        static::bootKernel();
        $this->handler = static::$container->get('oka_notifier_server.channel.clickatell_handler');
    }

    /**
     * @covers
     */
    public function testThatHandlerSupportsChannel()
    {
        $this->assertEquals(true, $this->handler->supports(new Notification(['sms', 'clickatell'], Address::create('test'), Address::create('test'), 'Hello World!')));
        $this->assertEquals(false, $this->handler->supports(new Notification(['sms'], Address::create('test'), Address::create('test'), 'Hello World!')));
    }
}
