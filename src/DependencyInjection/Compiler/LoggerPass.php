<?php

namespace Oka\Notifier\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class LoggerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('oka_notifier_server.logger_id')) {
            return;
        }

        if (false === $container->has($container->getParameter('oka_notifier_server.logger_id'))) {
            throw new \InvalidArgumentException(sprintf('Invalid service "%s" given.', $container->getParameter('oka_notifier_server.logger_id')));
        }

        // Configure "oka_notifier_server.messenger.notification_handler" service
        $notificationHandlerDefinition = $container->getDefinition('oka_notifier_server.messenger.notification_handler');
        $notificationHandlerDefinition->replaceArgument(3, new Reference($container->getParameter('oka_notifier_server.logger_id')));
    }
}
