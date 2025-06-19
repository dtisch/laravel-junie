<?php

namespace Dcblogdev\Junie\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('junie');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('documents')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children()
            ->scalarNode('name')->end()
            ->booleanNode('enabled')->defaultTrue()->end()
            ->scalarNode('path')->end()
            ->end()
            ->end()
            ->end()
            ->scalarNode('output_path')->defaultValue('.junie')->end()
            ->end();

        return $treeBuilder;
    }
}
