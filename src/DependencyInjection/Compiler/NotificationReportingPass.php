<?php

namespace Oka\Notifier\ServerBundle\DependencyInjection\Compiler;

use Doctrine\Persistence\ObjectManager;
use Oka\Notifier\ServerBundle\OkaNotifierServerBundle;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class NotificationReportingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->has('oka_notifier_server.send_report_controller')) {
            return;
        }

        $registry = OkaNotifierServerBundle::$doctrineDrivers[$container->getParameter('oka_notifier_server.reporting.db_driver')]['registry'];

        if (false === $container->has($registry)) {
            throw new \InvalidArgumentException('To enable notification reporting controller you have to install the "doctrine/doctrine-bundle" or "doctrine/mongodb-odm-bundle".');
        }

        $container->setAlias('oka_notifier_server.reporting.doctrine_registry', new Alias($registry, false));
        $objectManagerDefinition = $container->setDefinition('oka_notifier_server.reporting.object_manager', new Definition(ObjectManager::class));
        $objectManagerDefinition->setFactory([new Reference('oka_notifier_server.reporting.doctrine_registry'), 'getManager']);

        $messageManagerDefinition = $container->getDefinition('oka_notifier_server.send_report_manager');
        $messageManagerDefinition->replaceArgument(0, new Reference('oka_notifier_server.reporting.object_manager'));

        // Configure "oka_notifier_server.messenger.notification_handler" service
        $notificationHandlerDefinition = $container->getDefinition('oka_notifier_server.messenger.notification_handler');
        $notificationHandlerDefinition->replaceArgument(1, new Reference('oka_notifier_server.send_report_manager'));
    }
}
