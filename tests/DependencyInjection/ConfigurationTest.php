<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\LoopBackApiBundle\Tests\DependencyInjection;

use Fidry\LoopBackApiBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * @coversDefaultClass Fidry\LoopBackApiBundle\DependencyInjection\Configuration
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    private static $defaultConfig = [
        'parameters' => [
            'filter'        => 'filter',
            'search_filter' => 'where',
            'order_filter'  => 'order',
        ],
    ];

    /**
     * @cover ::getConfigTreeBuilder
     */
    public function testDefaultConfig()
    {
        $configuration = new Configuration();
        $treeBuilder = $configuration->getConfigTreeBuilder();
        $processor = new Processor();
        $config = $processor->processConfiguration(
            $configuration,
            [
                'loopback_api' => []
            ]
        );
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ConfigurationInterface', $configuration);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $treeBuilder);
        $this->assertEquals(self::$defaultConfig, $config);
    }
}
