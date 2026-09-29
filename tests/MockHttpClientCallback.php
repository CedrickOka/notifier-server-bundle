<?php

namespace Oka\Notifier\ServerBundle\Tests;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class MockHttpClientCallback
{
    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        switch (true) {
            case 'POST' === $method && 'https://api.clickatell.com/rest/message' === $url:
                return new MockResponse(
                    <<<EOF
{
    "data": {
        "message": [
            {
                "accepted": true,
                "to": "2799900001",
                "apiMessageId": "a55b8f8d56f33440e993aa614c68bf8b"
            },
            {
                "accepted": true,
                "to": "2799900002",
                "apiMessageId": "7f1d32762f6db11f3b7d2aaca2aaf362"
            }
        ]
    }
}
EOF,
                    ['http_code' => 202, 'response_headers' => ['Content-Type' => 'application/json']]
                );

            case 'POST' === $method && 'https://api.infobip.com/sms/2/text/advanced' === $url:
                return new MockResponse(
                    <<<EOF
{
    "bulkId": "2034072219640523072",
    "messages": [
        {
            "messageId": "2250be2d4219-3af1-78856-aabe-1362af1edfd2",
            "status": {
                "description": "Message sent to next instance",
                "groupId": 1,
                "groupName": "PENDING",
                "id": 26,
                "name": "PENDING_ACCEPTED"
            },
            "to": "41793026727"
        }
    ]
}
EOF,
                    ['response_headers' => ['Content-Type' => 'application/json']]
                );

            case 'POST' === $method && str_starts_with($url, 'https://api.wirepick.com/httpsms/send'):
                return new MockResponse(
                    <<<EOF
<?xml version="1.0" encoding="utf-8" ?>
<messages>
    <sms>
        <msgid>123456789</msgid>
        <phone>260776562302</phone>
        <country>Zambia</country>
        <status>ACT</status>
    </sms>
</messages>
EOF,
                    ['response_headers' => ['Content-Type' => 'application/xml']]
                );
        }
    }
}
