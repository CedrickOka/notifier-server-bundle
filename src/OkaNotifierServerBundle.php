<?php
namespace Oka\Notifier\ServerBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Oka\Notifier\ServerBundle\DependencyInjection\Compiler\CheckFirebaseMessagingEnabledPass;
use Oka\Notifier\ServerBundle\DependencyInjection\Compiler\CheckGuzzleHttpEnabledPass;
use Oka\Notifier\ServerBundle\DependencyInjection\Compiler\CheckMailerEnabledPass;
use Oka\Notifier\ServerBundle\DependencyInjection\Compiler\CheckPaginationEnabledPass;
use Oka\Notifier\ServerBundle\DependencyInjection\Compiler\LoggerPass;
use Oka\Notifier\ServerBundle\DependencyInjection\Compiler\MessengerPass;
use Oka\Notifier\ServerBundle\DependencyInjection\Compiler\NotificationReportingPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class OkaNotifierServerBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
    
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        $this->addRegisterMappingsPass($container);
        
        $container->addCompilerPass(new CheckMailerEnabledPass());
        $container->addCompilerPass(new CheckGuzzleHttpEnabledPass());
        $container->addCompilerPass(new CheckFirebaseMessagingEnabledPass());
        $container->addCompilerPass(new CheckPaginationEnabledPass());
        $container->addCompilerPass(new NotificationReportingPass());
        $container->addCompilerPass(new MessengerPass());
        $container->addCompilerPass(new LoggerPass());
    }
    
    private function addRegisterMappingsPass(ContainerBuilder $container)
    {
        $mapping = [realpath(__DIR__.'/../config/doctrine-mapping') => 'Oka\Notifier\ServerBundle\Model'];
        
        if (true === class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mapping, array('oka_notifier_server.reporting.model_manager_name'), 'oka_notifier_server.reporting.backend_type_orm'));
        }
        
        if (true === class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mapping, array('oka_notifier_server.reporting.model_manager_name'), 'oka_notifier_server.reporting.backend_type_mongodb'));
        }
    }
}
