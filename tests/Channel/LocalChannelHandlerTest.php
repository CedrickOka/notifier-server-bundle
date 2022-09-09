<?php

namespace Oka\Notifier\ServerBundle\Tests\Channel;

use Oka\Notifier\Message\Address;
use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Tests\Document\Message;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class LocalChannelHandlerTest extends KernelTestCase
{
    /**
     * @var \Oka\Notifier\ServerBundle\Channel\LocalChannelHandler
     */
    private $handler;

    public static function setUpBeforeClass(): void
    {
        static::bootKernel();

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = static::$container->get('doctrine_mongodb.odm.document_manager');
        $dm->createQueryBuilder(Message::class)
            ->remove()
            ->getQuery()
            ->execute();
    }

    public function setUp(): void
    {
        static::bootKernel();

        $this->handler = static::$container->get('oka_notifier_server.channel.local_handler');
    }

    /**
     * @covers
     */
    public function testThatHandlerSupportsChannel()
    {
        $this->assertEquals(true, $this->handler->supports(new Notification(['local'], Address::create('test'), Address::create('test'), 'Hello World!')));
        $this->assertEquals(false, $this->handler->supports(new Notification(['clickatell'], Address::create('test'), Address::create('test'), 'Hello World!')));
    }

    /**
     * @covers
     *
     * @doesNotPerformAssertions
     */
    public function testThatHandlerCanWeSendNotification(): void
    {
        $this->handler->send(new Notification(['local'], Address::create('0707'), Address::create(getenv('RECEIVER_ADDRESS')), 'Hello World!'));
    }
}
