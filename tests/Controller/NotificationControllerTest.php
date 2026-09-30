<?php

namespace Oka\Notifier\ServerBundle\Tests\Controller;

use Oka\Notifier\ServerBundle\Test\WebTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class NotificationControllerTest extends WebTestCase
{
    /**
     * @covers
     */
    public function testCanSendNotificatonOnSMSChannel()
    {
        $this->client->request('POST', '/v1/rest/notifications', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{"notifications": [{"channels": ["clickatell"], "sender": "Notifier", "receiver": "+2250707070707", "message": "Hello World!"}]}');

        $this->assertResponseStatusCodeSame(204);
    }

    /**
     * @covers
     */
    public function testCannotSendNotificatonWithAWrongReceiver()
    {
        $this->client->request('POST', '/v1/rest/notifications', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{"notifications": [{"channels": ["clickatell"], "sender": "Notifier", "receiver": {"name": "2250707070707"}, "message": "Hello World!"}]}');

        $this->assertResponseStatusCodeSame(400);
    }
}
