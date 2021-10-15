<?php

namespace Oka\Notifier\ServerBundle\DependencyInjection;

use Oka\Notifier\ServerBundle\Model\SendReportInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('oka_notifier_server');
        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('channels')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('email')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                        ->end()

                        ->arrayNode('sms')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                        ->end()

                        ->arrayNode('smpp')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return $this->validateChannel($v, ['dsn']);
                                })
                                ->thenInvalid($this->createInvalidChannelMessage('smpp', ['dsn']))
                            ->end()
                            ->children()
                                ->scalarNode('dsn')
                                    ->defaultNull()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('infobip')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return $this->validateChannel($v, ['url', 'api_key']);
                                })
                                ->thenInvalid($this->createInvalidChannelMessage('infobip', ['url', 'api_key']))
                            ->end()
                            ->children()
                                ->scalarNode('url')
                                    ->defaultNull()
                                ->end()

                                ->scalarNode('api_key')
                                    ->defaultNull()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('clickatell')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return $this->validateChannel($v, ['url', 'token']);
                                })
                                ->thenInvalid($this->createInvalidChannelMessage('clickatell', ['url', 'token']))
                            ->end()
                            ->children()
                                ->scalarNode('url')
                                    ->defaultNull()
                                ->end()

                                ->scalarNode('token')
                                    ->defaultNull()
                                ->end()
                            ->end()
                            ->end()
                            
                            ->arrayNode('wirepick')
                                ->addDefaultsIfNotSet()
                                ->canBeEnabled()
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return $this->validateChannel($v, ['client_id', 'password']);
                                    })
                                    ->thenInvalid($this->createInvalidChannelMessage('wirepick', ['client_id', 'password']))
                                ->end()
                                ->children()
                                    ->scalarNode('client_id')
                                        ->defaultNull()
                                    ->end()
                                    
                                    ->scalarNode('password')
                                        ->defaultNull()
                                    ->end()
                                ->end()
                            ->end()

                        ->arrayNode('firebase')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return $this->validateChannel($v, ['messaging_id']);
                                })
                                ->thenInvalid($this->createInvalidChannelMessage('firebase', ['messaging_id']))
                            ->end()
                            ->children()
                                ->scalarNode('messaging_id')
                                    ->defaultValue('kreait_firebase.default.messaging')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('messenger')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('bus_id')
                            ->cannotBeEmpty()
                            ->defaultValue('messenger.default_bus')
                        ->end()

                        ->scalarNode('queue_name')
                            ->cannotBeEmpty()
                            ->defaultValue('messages.notifier')
                        ->end()

                        ->arrayNode('binding_keys')
                            ->scalarPrototype()
                                ->cannotBeEmpty()
                                ->defaultValue(['notification'])
                            ->end()
                        ->end()

                        ->scalarNode('default_publish_routing_key')
                            ->cannotBeEmpty()
                            ->defaultValue('notification')
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('reporting')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->validate()
                        ->ifTrue(function ($class) {
                            return true === $class['enabled'] && !$class['class_name'];
                        })
                        ->thenInvalid('The confguration value "oka_notifier_server.reporting.class_name" cannot be empty if reporting is enabled.')
                    ->end()
                    ->children()
                        ->enumNode('db_driver')
                            ->cannotBeEmpty()
                            ->values(['mongodb', 'orm'])
                            ->defaultValue('mongodb')
                        ->end()

                        ->scalarNode('model_manager_name')
                            ->defaultNull()
                        ->end()

                        ->scalarNode('class_name')
                            ->defaultNull()
                            ->validate()
                                ->ifTrue(function ($class) {
                                    return null !== $class && !(new \ReflectionClass($class))->implementsInterface(SendReportInterface::class);
                                })
                                ->thenInvalid('The confguration value "oka_notifier_server.reporting.class_name" is not valid because "%s" class given must implement '.SendReportInterface::class.'.')
                            ->end()
                        ->end()

                        ->scalarNode('pagination_manager_name')
                            ->defaultValue('send_report')
                        ->end()
                    ->end()
                ->end()

                ->scalarNode('logger_id')
                    ->defaultNull()
                ->end()
            ->end();

        return $treeBuilder;
    }

    protected function validateChannel($value, array $options = []): bool
    {
        if (false === $value['enabled']) {
            return false;
        }

        foreach ($options as $option) {
            if (false === isset($value[$option]) || null === $value[$option] || '' === $value[$option]) {
                return true;
            }
        }

        return false;
    }

    private function createInvalidChannelMessage(string $channel, array $options = []): string
    {
        return sprintf('The "oka_notifier_server.channels.%s" configuration cannot be enabled with the following options "%s" invalid.', $channel, implode(', ', $options));
    }
}
