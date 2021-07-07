<?php

namespace Oka\Notifier\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class MessengerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('oka_notifier_server.notification_controller')) {
            return;
        }

        if (false === $container->has($container->getParameter('oka_notifier_server.messenger.bus_id'))) {
            throw new \InvalidArgumentException(sprintf('Invalid service "%s" given.', $container->getParameter('oka_notifier_server.messenger.bus_id')));
        }

        $notificationControllerDefinition = $container->getDefinition('oka_notifier_server.notification_controller');
        $notificationControllerDefinition->replaceArgument(0, new Reference($container->getParameter('oka_notifier_server.messenger.bus_id')));
    }
}
