<?php

namespace Dkbs\DkbsignBundle;

use Dkbs\DkbsignBundle\Client\Client;
use Dkbs\DkbsignBundle\Signature\AutoSignature;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class DkbsignBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return __DIR__;
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('api_url')->defaultValue('https://api.dkbsigns.com')->cannotBeEmpty()->end()
                ->scalarNode('api_token')->isRequired()->cannotBeEmpty()->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        $services->set(Client::class)
            ->autowire()
            ->arg('$apiUrl', $config['api_url'])
            ->arg('$apiToken', $config['api_token']);

        $services->set(AutoSignature::class)
            ->autowire();
    }
}
