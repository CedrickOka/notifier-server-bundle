<?php

namespace Oka\Notifier\ServerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class CheckPaginationEnabledPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (true === $container->has('oka_notifier_server.send_report_controller') && false === $container->has('oka_pagination.pagination_manager')) {
            throw new \LogicException('To enable notification reporting controller you have to install the "coka/pagination-bundle".');
        }
    }
}
