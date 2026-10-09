<?php

namespace Oka\Notifier\ServerBundle\Tests\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Oka\Notifier\ServerBundle\Test\WebTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SendReportControllerTest extends WebTestCase
{
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

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $metaData = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($em);
        $schemaTool->updateSchema($metaData);
    }
}
