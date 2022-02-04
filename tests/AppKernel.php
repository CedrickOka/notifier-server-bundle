<?php

namespace Oka\Notifier\ServerBundle\Tests;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Oka\InputHandlerBundle\OkaInputHandlerBundle(),
            new \Oka\PaginationBundle\OkaPaginationBundle(),
            new \Kreait\Firebase\Symfony\Bundle\FirebaseBundle(),
            new \Oka\Notifier\ServerBundle\OkaNotifierServerBundle(),
        ];

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // We don't need that Environment stuff, just one config
        $loader->load(__DIR__.'/config.yaml');
    }
}
