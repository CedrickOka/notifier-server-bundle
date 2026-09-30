<?php

namespace Oka\Notifier\ServerBundle\Tests\Controller;

use Oka\Notifier\ServerBundle\Test\Document\SendReport;
use Oka\Notifier\ServerBundle\Test\WebTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SendReportControllerTest extends WebTestCase
{
    public static function setUpBeforeClass(): void
    {
        static::bootKernel();

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = static::getContainer()->get('doctrine_mongodb.odm.document_manager');
        $dm->createQueryBuilder(SendReport::class)
            ->remove()
            ->getQuery()
            ->execute();
    }

    /**
     * @covers
     */
    public function testCanListSendReportNotificaton()
    {
        $this->client->request('GET', '/v1/rest/send-reports');
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals(0, count($content['items']));
    }
}
