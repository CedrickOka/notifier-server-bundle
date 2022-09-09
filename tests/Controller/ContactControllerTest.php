<?php

namespace Oka\Notifier\ServerBundle\Tests\Controller;

use Oka\Notifier\ServerBundle\Tests\Document\Contact;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class ContactControllerTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    public static function setUpBeforeClass(): void
    {
        static::bootKernel();

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = static::$container->get('doctrine_mongodb.odm.document_manager');
        $dm->createQueryBuilder(Contact::class)
            ->remove()
            ->getQuery()
            ->execute();

        static::ensureKernelShutdown();
    }

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @covers
     */
    public function testCanCreateContact()
    {
        $this->client->request(
            'POST',
            '/v1/rest/contacts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            <<<EOF
{
	"channel": "firebase",
	"name": "johndoe",
	"addresses": [
        {"value": "0707070707"}
    ]
}
EOF
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(201);
        $this->assertEquals('firebase', $content['channel']);
        $this->assertEquals('johndoe', $content['name']);
        $this->assertEquals('0707070707', $content['addresses'][0]['value']);

        return $content;
    }

    /**
     * @covers
     *
     * @depends testCanCreateContact
     */
    public function testCanListContact(array $depend)
    {
        $this->client->request('GET', '/v1/rest/contacts');
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals(1, count($content['items']));
        $this->assertEquals('firebase', $content['items'][0]['channel']);
        $this->assertEquals('johndoe', $content['items'][0]['name']);

        return $depend;
    }

    /**
     * @covers
     *
     * @depends testCanListContact
     */
    public function testCanReadContact(array $depend)
    {
        $this->client->request('GET', sprintf('/v1/rest/contacts/%s', $depend['id']));
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('firebase', $content['channel']);
        $this->assertEquals('johndoe', $content['name']);
        $this->assertEquals('0707070707', $content['addresses'][0]['value']);

        return $content;
    }

    /**
     * @covers
     *
     * @depends testCanReadContact
     */
    public function testCanUpdateContact(array $depend)
    {
        $this->client->request(
            'PATCH',
            sprintf('/v1/rest/contacts/%s', $depend['id']),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            <<<EOF
{
	"addresses": [
        {
            "value": "0707070707",
            "name": "orange"
        }
    ]
}
EOF
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('firebase', $content['channel']);
        $this->assertEquals('johndoe', $content['name']);
        $this->assertEquals('0707070707', $content['addresses'][0]['value']);
        $this->assertEquals('orange', $content['addresses'][0]['name']);

        return $content;
    }

    /**
     * @covers
     *
     * @depends testCanUpdateContact
     */
    public function testCanDeleteContact(array $depend)
    {
        $this->client->request('DELETE', sprintf('/v1/rest/contacts/%s', $depend['id']));

        $this->assertResponseStatusCodeSame(204);
    }
}
