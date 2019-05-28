<?php

namespace Purjus\LocoImporterBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('purjus_loco_importer');

        $rootNode
            ->children()
                ->arrayNode('projects')->isRequired()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('key')->end()
                            ->scalarNode('file')->end()
                            ->scalarNode('format')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
