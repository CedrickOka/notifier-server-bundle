<?php

namespace Oka\Notifier\ServerBundle\Tests;

use Oka\Notifier\Message\Address;
use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\MessageHandler\NotificationHandler;
use Oka\Notifier\ServerBundle\Tests\Document\Message;
use Oka\Notifier\ServerBundle\Tests\Document\SendReport;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class IntegrationTest extends KernelTestCase
{
    /**
     * @var \Oka\Notifier\ServerBundle\MessageHandler\NotificationHandler
     */
    private $handler;

    /**
     * @var \Oka\Notifier\ServerBundle\Service\MessageManager
     */
    private $messageManager;

    /**
     * @var \Oka\Notifier\ServerBundle\Service\SendReportManager
     */
    private $sendReportManager;

    public static function setUpBeforeClass(): void
    {
        static::bootKernel();

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = static::$container->get('doctrine_mongodb.odm.document_manager');
        $dm->createQueryBuilder(Message::class)->remove()->getQuery()->execute();
        $dm->createQueryBuilder(SendReport::class)->remove()->getQuery()->execute();
    }

    public function setUp(): void
    {
        $this->messageManager = static::$container->get('oka_notifier_server.message_manager');
        $this->sendReportManager = static::$container->get('oka_notifier_server.send_report_manager');
        $this->handler = new NotificationHandler([static::$container->get('oka_notifier_server.channel.local_handler')], $this->sendReportManager);
    }

    /**
     * @covers
     */
    public function testItSendsLocalNotification()
    {
        $this->handler->__invoke(new Notification(['local'], Address::create('test'), Address::create('test'), 'Hello World!'));

        $messages = $this->messageManager->findBy([]);
        $sendReports = $this->sendReportManager->findBy([]);

        $this->assertEquals(1, count($messages));
        $this->assertEquals(1, count($sendReports));

        $this->assertEquals('test', $messages[0]->getFrom()->getValue());
        $this->assertEquals('test', $messages[0]->getTo()->getValue());
        $this->assertEquals(null, $messages[0]->getSubject());
        $this->assertEquals('Hello World!', $messages[0]->getBody());
    }
}
