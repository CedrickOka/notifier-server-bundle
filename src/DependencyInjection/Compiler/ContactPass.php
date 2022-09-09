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
class ContactPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->has('oka_notifier_server.contact_controller')) {
            return;
        }

        $registry = OkaNotifierServerBundle::$doctrineDrivers[$container->getParameter('oka_notifier_server.contact.db_driver')]['registry'];

        if (false === $container->has($registry)) {
            throw new \InvalidArgumentException('To enable "contact" you have to install the "doctrine/doctrine-bundle" or "doctrine/mongodb-odm-bundle".');
        }

        $container->setAlias('oka_notifier_server.contact.doctrine_registry', new Alias($registry, false));
        $objectManagerDefinition = $container->setDefinition('oka_notifier_server.contact.object_manager', new Definition(ObjectManager::class));
        $objectManagerDefinition->setFactory([new Reference('oka_notifier_server.contact.doctrine_registry'), 'getManager']);

        $contactManagerDefinition = $container->getDefinition('oka_notifier_server.contact_manager');
        $contactManagerDefinition->replaceArgument(0, new Reference('oka_notifier_server.contact.object_manager'));
    }
}
