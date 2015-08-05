<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\LoopBackApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * The configuration of the bundle.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('loopback_api');

        $rootNode
            ->children()
                ->arrayNode('parameters')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('filter')->defaultValue('filter')->info('Keyword for the filter.')->end()
                        ->scalarNode('search_filter')->defaultValue('where')->info('Keyword for the search filter.')->end()
                        ->scalarNode('order_filter')->defaultValue('order')->info('Keyword for the search filter.')
            ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
