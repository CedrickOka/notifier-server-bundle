<?php
namespace Oka\Notifier\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class CheckMailerEnabledPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (true === $container->hasDefinition('oka_notifier_server.channel.email_handler') && false === $container->has('mailer')) {
            throw new \LogicException('To enable mail channel handler you have to install the "symfony/mailer".');
        }
    }
}
