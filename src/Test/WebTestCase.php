<?php

namespace Oka\Notifier\ServerBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected $client;

    protected function setUp(): void
    {
        static::ensureKernelShutdown();

        $this->client = static::createClient();
    }
}
