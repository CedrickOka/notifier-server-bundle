<?php

namespace Oka\Notifier\ServerBundle\Tests\Channel;

use Oka\Notifier\Message\Address;
use Oka\Notifier\Message\Notification;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class FirebaseChannelHandlerTest extends KernelTestCase
{
    /**
     * @var \Oka\Notifier\ServerBundle\Channel\ClickatellChannelHandler
     */
    private $handler;

    public function setUp(): void
    {
        static::bootKernel();
        $this->handler = static::getContainer()->get('oka_notifier_server.channel.firebase_handler');
    }

    /**
     * @covers
     */
    public function testThatHandlerSupportsChannel(): void
    {
        $this->markTestSkipped();
        $this->assertEquals(true, $this->handler->supports(new Notification(['firebase'], Address::create('test'), Address::create('test'), 'Hello World!')));
        $this->assertEquals(false, $this->handler->supports(new Notification(['sms'], Address::create('test'), Address::create('test'), 'Hello World!')));

        $this->handler->send(new Notification(
            ['firebase'],
            Address::create(getenv('SENDER_ADDRESS')),
            Address::create(
                'fDfSccSXcO46YanXFYPLvP:APA91bE31uyrM3g8aC22UKGDGY5EIUTOEBBFDBKaSrZdASOqAjw4j1ktl9CBCraJAroH5l6aV4dHFgk6sfurWQDlfRhF9E9BqkLP1uxl89GNFCJczC39au8',
                'token'
            ),
            'Hello World!'
        ));
    }
}
