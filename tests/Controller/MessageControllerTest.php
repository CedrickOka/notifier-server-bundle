<?php

namespace Oka\Notifier\ServerBundle\Tests\Controller;

use Oka\Notifier\Message\Address;
use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Test\Document\Message;
use Oka\Notifier\ServerBundle\Test\WebTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class MessageControllerTest extends WebTestCase
{
    public static function setUpBeforeClass(): void
    {
        static::bootKernel();

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = static::getContainer()->get('doctrine_mongodb.odm.document_manager');
        $dm->createQueryBuilder(Message::class)
            ->remove()
            ->getQuery()
            ->execute();

        /** @var \Oka\Notifier\ServerBundle\Channel\LocalChannelHandler $handler */
        $handler = static::getContainer()->get('oka_notifier_server.channel.local_handler');
        $handler->send(new Notification(['local'], Address::create('test'), Address::create('test'), 'Hello World!'));
    }

    /**
     * @covers
     */
    public function testCanListMessageNotificaton()
    {
        $this->client->request('GET', '/v1/rest/messages');
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals(1, count($content['items']));
    }
}
