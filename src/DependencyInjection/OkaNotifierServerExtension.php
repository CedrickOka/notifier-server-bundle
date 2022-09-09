<?php

namespace Oka\Notifier\ServerBundle\DependencyInjection;

use Oka\Notifier\Message\Notification;
use Oka\Notifier\ServerBundle\Channel\ChannelHandlerInterface;
use Oka\Notifier\ServerBundle\Channel\ClickatellChannelHandler;
use Oka\Notifier\ServerBundle\Channel\EmailChannelHandler;
use Oka\Notifier\ServerBundle\Channel\FirebaseChannelHandler;
use Oka\Notifier\ServerBundle\Channel\InfobipChannelHandler;
use Oka\Notifier\ServerBundle\Channel\LocalChannelHandler;
use Oka\Notifier\ServerBundle\Channel\SmppChannelHandler;
use Oka\Notifier\ServerBundle\Channel\SmsChannelHandler;
use Oka\Notifier\ServerBundle\Channel\SmsChannelHandlerInterface;
use Oka\Notifier\ServerBundle\Channel\WirepickChannelHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class OkaNotifierServerExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        if (true === $this->isConfigEnabled($container, $config['channels']['local'])) {
            $this->configureDbal($container, 'channel.local', $config['channels']['local']);
            $loader->load('message.yaml');

            $localChannelDefinition = $container->setDefinition('oka_notifier_server.channel.local_handler'::class, new Definition(
                LocalChannelHandler::class,
                [new Reference('oka_notifier_server.message_manager')]
            ));
            $localChannelDefinition->addTag('oka_notifier_server.channel_handler');
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['email'])) {
            $emailChannelDefinition = $container->setDefinition('oka_notifier_server.channel.email_handler'::class, new Definition(
                EmailChannelHandler::class,
                [new Reference('mailer')]
            ));
            $emailChannelDefinition->addTag('oka_notifier_server.channel_handler');
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['sms'])) {
            $smsChannelDefinition = $container->setDefinition('oka_notifier_server.channel.sms_handler', new Definition(
                SmsChannelHandler::class,
                [new TaggedIteratorArgument('oka_notifier_server.channel_handler_sms')]
            ));
            $smsChannelDefinition->addTag('oka_notifier_server.channel_handler');
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['smpp'])) {
            $smppChannelDefinition = $container->setDefinition('oka_notifier_server.channel.smpp_handler', new Definition(
                SmppChannelHandler::class,
                [$config['channels']['smpp']['dsn'], new Parameter('kernel.debug')]
            ));
            $smppChannelDefinition->addTag('oka_notifier_server.channel_handler');
            $smppChannelDefinition->addTag('oka_notifier_server.channel_handler_sms', ['priority' => 15]);
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['infobip'])) {
            $infobipChannelDefinition = $container->setDefinition('oka_notifier_server.channel.infobip_handler', new Definition(
                InfobipChannelHandler::class,
                [$config['channels']['infobip']['url'], $config['channels']['infobip']['api_key'], new Parameter('kernel.debug')]
            ));
            $infobipChannelDefinition->addTag('oka_notifier_server.channel_handler');
            $infobipChannelDefinition->addTag('oka_notifier_server.channel_handler_sms', ['priority' => 10]);
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['clickatell'])) {
            $clickatellChannelDefinition = $container->setDefinition('oka_notifier_server.channel.clickatell_handler', new Definition(
                ClickatellChannelHandler::class,
                [$config['channels']['clickatell']['url'], $config['channels']['clickatell']['token'], new Parameter('kernel.debug')]
            ));
            $clickatellChannelDefinition->addTag('oka_notifier_server.channel_handler');
            $clickatellChannelDefinition->addTag('oka_notifier_server.channel_handler_sms', ['priority' => 5]);
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['wirepick'])) {
            $wirepickChannelDefinition = $container->setDefinition('oka_notifier_server.channel.wirepick_handler', new Definition(
                WirepickChannelHandler::class,
                [$config['channels']['wirepick']['client_id'], $config['channels']['wirepick']['password'], new Parameter('kernel.debug')]
            ));
            $wirepickChannelDefinition->addTag('oka_notifier_server.channel_handler');
            $wirepickChannelDefinition->addTag('oka_notifier_server.channel_handler_sms', ['priority' => 0]);
        }

        if (true === $this->isConfigEnabled($container, $config['channels']['firebase'])) {
            $firebaseChannelDefinition = $container->setDefinition('oka_notifier_server.channel.firebase_handler', new Definition(
                FirebaseChannelHandler::class,
                [new Reference($config['channels']['firebase']['messaging_id'])]
            ));
            $firebaseChannelDefinition->addTag('oka_notifier_server.channel_handler');
        }

        $container->setParameter('oka_notifier_server.messenger.bus_id', $config['messenger']['bus_id']);

        // Contact notification configuration
        if (true === $this->isConfigEnabled($container, $config['contact'])) {
            $this->configureDbal($container, 'contact', $config['contact']);
            $loader->load('contact.yaml');
        }

        // Reporting notification configuration
        if (true === $this->isConfigEnabled($container, $config['reporting'])) {
            $this->configureDbal($container, 'reporting', $config['reporting']);
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
                                    'binding_keys' => $config['messenger']['binding_keys'],
                                ],
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
                    ],
                ],
                'routing' => [Notification::class => $config['messenger']['default_publish_routing_key']],
            ],
        ]);
    }

    private function configureDbal(ContainerBuilder $container, string $configName, array $config): void
    {
        $container->setParameter(sprintf('oka_notifier_server.%s.db_driver', $configName), $config['db_driver']);
        $container->setParameter(sprintf('oka_notifier_server.%s.backend_type_%s', $configName, $config['db_driver']), true);
        $container->setParameter(sprintf('oka_notifier_server.%s.model_manager_name', $configName), $config['model_manager_name']);
        $container->setParameter(sprintf('oka_notifier_server.%s.class_name', $configName), $config['class_name']);
        $container->setParameter(sprintf('oka_notifier_server.%s.pagination_manager_name', $configName), $config['pagination_manager_name']);
    }
}
