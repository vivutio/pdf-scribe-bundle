<?php

declare(strict_types=1);

namespace Vivutio\PdfScribeBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Vivutio\PdfScribeBundle\Contract\PdfGeneratorInterface;
use Vivutio\PdfScribeBundle\Service\PdfGeneratorService;

class PdfScribeBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('binary_path')
                    ->defaultValue('/Applications/Google Chrome.app/Contents/MacOS/Google Chrome')
                    ->info('Path to Chrome/Chromium binary')
                ->end()
                ->integerNode('timeout')
                    ->defaultValue(120)
                    ->info('Process timeout in seconds')
                ->end()
                ->arrayNode('options')
                    ->info('Default Chrome PDF options')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                    ->defaultValue([
                        'print-background' => true,
                        'no-pdf-header-footer' => true,
                    ])
                ->end()
            ->end();
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $services = $container->services();

        $services->set(PdfGeneratorService::class)
            ->args([
                $config['binary_path'],
                $config['timeout'],
                $config['options'],
            ])
            ->public();

        $services->alias(PdfGeneratorInterface::class, PdfGeneratorService::class)
            ->public();
    }
}
