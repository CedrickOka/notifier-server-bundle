<?php
namespace Oka\Notifier\ServerBundle\Tests\Channel;

use Oka\Notifier\Message\Address;
use Oka\Notifier\Message\Notification;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SmppChannelHandlerTest extends KernelTestCase
{
    /**
     * @var \Oka\Notifier\ServerBundle\Channel\SmppChannelHandler
     */
    private $handler;
    
    public function setUp() :void
    {
        static::bootKernel();
        $this->handler = static::$container->get('oka_notifier_server.channel.smpp_handler');
    }
    
    /**
     * @covers
     */
    public function testThatHandlerSupportsChannel()
    {
        $this->assertEquals(true, $this->handler->supports(new Notification(['smpp', 'clickatell'], Address::create('test'), Address::create('test'), 'Hello World!')));
        $this->assertEquals(false, $this->handler->supports(new Notification(['clickatell'], Address::create('test'), Address::create('test'), 'Hello World!')));
    }
    
    /**
     * @covers
     * @doesNotPerformAssertions
     */
    public function testThatHandlerCanWeSendNotification() :void
    {
        $this->handler->send(new Notification(['smpp'], Address::create('0707'), Address::create('09970126'), 'Hello World!'));
    }
}
