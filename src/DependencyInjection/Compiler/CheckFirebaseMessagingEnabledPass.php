<?php
namespace Oka\Notifier\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class CheckFirebaseMessagingEnabledPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (true === $container->hasDefinition('oka_notifier_server.channel.firebase_handler') && false === class_exists('Kreait\Firebase\Messaging')) {
            throw new \LogicException('To enable firebase channel handler you have to install the "kreait/firebase-bundle".');
        }
    }
}
