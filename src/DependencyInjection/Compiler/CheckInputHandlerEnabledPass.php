<?php

namespace Oka\Notifier\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class CheckInputHandlerEnabledPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ((true === $container->has('oka_notifier_server.message_controller')
            || true === $container->has('oka_notifier_server.contact_controller')
            || true === $container->has('oka_notifier_server.send_report_controller'))
            && false === $container->has('oka_input_handler.request_listener')) {
            throw new \LogicException('To enable contact, message or reporting controllers you have to install the "coka/input-handler-bundle".');
        }
    }
}
