<?php
namespace Oka\Notifier\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class LoggerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->has($container->getParameter('oka_notifier_server.logger_id'))) {
            throw new \InvalidArgumentException(sprintf('Invalid service "%s" given.', $container->getParameter('oka_notifier_server.logger_id')));
        }
        
        $definition = $container->getDefinition('oka_notifier_server.messenger.notification_handler');
        $definition->replaceArgument(2, new Reference($container->getParameter('oka_notifier_server.logger_id')));
    }
}
