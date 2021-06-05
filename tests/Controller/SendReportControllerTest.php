<?php
namespace Oka\Notifier\ServerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SendReportControllerTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;
    
    public function setUp(): void
    {
        $this->client = static::createClient();
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
