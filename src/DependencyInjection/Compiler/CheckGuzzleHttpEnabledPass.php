<?php

namespace Oka\Notifier\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class CheckGuzzleHttpEnabledPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ((true === $container->hasDefinition('oka_notifier_server.channel.firebase_handler') ||
            true === $container->hasDefinition('oka_notifier_server.channel.clickatell_handler')) &&
            false === class_exists('GuzzleHttp\Client')) {
            throw new \LogicException('To enable infobip or clickatell channel handler you have to install the "guzzlehttp/guzzle".');
        }
    }
}
