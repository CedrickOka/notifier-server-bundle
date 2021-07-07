<?php

namespace Oka\Notifier\ServerBundle\DependencyInjection;

use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Channel\ChannelHandlerInterface;
use Oka\Notifier\ServerBundle\Channel\ClickatellChannelHandler;
use Oka\Notifier\ServerBundle\Channel\EmailChannelHandler;
use Oka\Notifier\ServerBundle\Channel\FirebaseChannelHandler;
use Oka\Notifier\ServerBundle\Channel\InfobipChannelHandler;
use Oka\Notifier\ServerBundle\Channel\SmppChannelHandler;
use Oka\Notifier\ServerBundle\Channel\SmsChannelHandler;
use Oka\Notifier\ServerBundle\Channel\SmsChannelHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class OkaNotifierServerExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var array
     */
    public static $doctrineDrivers = [
        'orm' => [
            'registry' => 'doctrine',
            'tag' => 'doctrine.event_subscriber',
        ],
        'mongodb' => [
            'registry' => 'doctrine_mongodb',
            'tag' => 'doctrine_mongodb.odm.event_subscriber',
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        if (true === $this->isConfigEnabled($container, $config['channels']['email'])) {
            $emailChannelDefinition = $container->setDefinition('oka_notifier_server.channel.email_handler'::class, new Definition(
                EmailChannelHandler::class,
                [new Reference('mailer')]
            ));
            $emailChannelDefinition->addTag('oka_notifier_server.channel_handler');
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['sms'])) {
            $emailChannelDefinition = $container->setDefinition('oka_notifier_server.channel.sms_handler', new Definition(
                SmsChannelHandler::class,
                [new TaggedIteratorArgument('oka_notifier_server.channel_handler_sms')]
            ));
            $emailChannelDefinition->addTag('oka_notifier_server.channel_handler');
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['smpp'])) {
            $emailChannelDefinition = $container->setDefinition('oka_notifier_server.channel.smpp_handler', new Definition(
                SmppChannelHandler::class,
                [$config['channels']['smpp']['dsn'], new Parameter('kernel.debug')]
            ));
            $emailChannelDefinition->addTag('oka_notifier_server.channel_handler');
            $emailChannelDefinition->addTag('oka_notifier_server.channel_handler_sms', ['priority' => 15]);
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['infobip'])) {
            $emailChannelDefinition = $container->setDefinition('oka_notifier_server.channel.infobip_handler', new Definition(
                InfobipChannelHandler::class,
                [$config['channels']['infobip']['url'], $config['channels']['infobip']['api_key'], new Parameter('kernel.debug')]
            ));
            $emailChannelDefinition->addTag('oka_notifier_server.channel_handler');
            $emailChannelDefinition->addTag('oka_notifier_server.channel_handler_sms', ['priority' => 10]);
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['clickatell'])) {
            $emailChannelDefinition = $container->setDefinition('oka_notifier_server.channel.clickatell_handler', new Definition(
                ClickatellChannelHandler::class,
                [$config['channels']['clickatell']['url'], $config['channels']['clickatell']['token'], new Parameter('kernel.debug')]
            ));
            $emailChannelDefinition->addTag('oka_notifier_server.channel_handler');
            $emailChannelDefinition->addTag('oka_notifier_server.channel_handler_sms', ['priority' => 5]);
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['firebase'])) {
            $emailChannelDefinition = $container->setDefinition('oka_notifier_server.channel.firebase_handler', new Definition(
                FirebaseChannelHandler::class,
                [new Reference($config['channels']['firebase']['messaging_id'])]
            ));
            $emailChannelDefinition->addTag('oka_notifier_server.channel_handler');
        }

        $container->setParameter('oka_notifier_server.messenger.bus_id', $config['messenger']['bus_id']);

        // Reporting notification configuration
        if (true === $this->isConfigEnabled($container, $config['reporting'])) {
            $container->setParameter('oka_notifier_server.reporting.db_driver', $config['reporting']['db_driver']);
            $container->setParameter('oka_notifier_server.reporting.backend_type_'.$config['reporting']['db_driver'], true);
            $container->setParameter('oka_notifier_server.reporting.model_manager_name', $config['reporting']['model_manager_name']);
            $container->setParameter('oka_notifier_server.reporting.class_name', $config['reporting']['class_name']);
            $container->setParameter('oka_notifier_server.reporting.pagination_manager_name', $config['reporting']['pagination_manager_name']);

            $loader->load('reporting.yaml');
        }

        if (null !== $config['logger_id']) {
            $container->setParameter('oka_notifier_server.logger_id', $config['logger_id']);
        }

        $container
            ->registerForAutoconfiguration(ChannelHandlerInterface::class)
            ->addTag('oka_notifier_server.channel_handler');

        $container
            ->registerForAutoconfiguration(SmsChannelHandlerInterface::class)
            ->addTag('oka_notifier_server.channel_handler_sms');
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $configs = $container->getParameterBag()->resolveValue($configs);
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->prependExtensionConfig('framework', [
            'messenger' => [
                'serializer' => [
                    'default_serializer' => 'messenger.transport.symfony_serializer',
                    'symfony_serializer' => [
                        'format' => 'json',
                        'context' => [],
                    ],
                ],
                'transports' => [
                    'notification' => [
                        'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%',
                        'options' => [
                            'queues' => [
                                $config['messenger']['queue_name'] => [
                                    'binding_keys' => $config['messenger']['binding_keys']
                                ]
                            ],
                            'exchange' => [
                                'type' => 'direct',
                                'default_publish_routing_key' => $config['messenger']['default_publish_routing_key'],
                            ],
                            'prefetch_count' => 1,
                            'retry_strategy' => [
                                'type' => 15000,
                                'multiplier' => 2,
                            ],
                        ],
                    ]
                ],
                'routing' => [Notification::class => $config['messenger']['default_publish_routing_key']],
            ]
        ]);
    }
}
