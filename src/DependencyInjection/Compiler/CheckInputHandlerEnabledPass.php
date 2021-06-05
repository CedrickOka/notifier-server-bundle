<?php
namespace Oka\Notifier\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class CheckInputHandlerEnabledPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ((true === $container->has('oka_notifier_server.notification_controller') ||
            true === $container->has('oka_notifier_server.send_report_controller')) &&
            false === $container->has('oka_input_handler.error_response.factory')) {
            throw new \LogicException('To enable notification controller you have to install the "coka/input-handler-bundle".');
        }
    }
}
