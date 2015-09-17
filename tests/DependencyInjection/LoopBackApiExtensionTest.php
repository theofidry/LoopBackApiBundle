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

use Dunglas\ApiBundle\Api\Resource;
use Fidry\LoopBackApiBundle\DependencyInjection\LoopBackApiExtension;
use Fidry\LoopBackApiBundle\Tests\Prophecy\DependencyInjectionArgument;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass Fidry\LoopBackApiBundle\DependencyInjection\LoopBackApiExtension
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class LoopBackApiExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::__construct
     */
    public function testConstruct()
    {
        $extension = new LoopBackApiExtension();
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Extension\ExtensionInterface', $extension);
        $this->assertInstanceOf(
            'Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface',
            $extension
        );
    }

    /**
     * Tests the default configuration with all the extensions disabled. Expects no driver loaded.
     */
    public function testLoad()
    {
        $containerBuilderProphecy = $this->getBaseDefaultContainerBuiderProphecy();

        /* @var ContainerBuilder $containerBuilder */
        $containerBuilder = $containerBuilderProphecy->reveal();

        $extension = new LoopBackApiExtension();

        $extension->load([], $containerBuilder);
    }

    /**
     * Gets a Prophecy object for the ContainerBuilder which includes the mandatory called on the services included in
     * the default config.
     *
     * @return ObjectProphecy
     */
    private function getBaseDefaultContainerBuiderProphecy()
    {
        $containerBuilderProphecy = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilderProphecy->setParameter('loopback_api.parameter.filter', 'filter')->shouldBeCalled();
        $containerBuilderProphecy->setParameter('loopback_api.parameter.order_filter', 'order')->shouldBeCalled();
        $containerBuilderProphecy->setParameter('loopback_api.parameter.search_filter', 'where')->shouldBeCalled();

        $containerBuilderProphecy->hasExtension('http://symfony.com/schema/dic/services')->shouldBeCalled();

        $containerBuilderProphecy
            ->addResource(DependencyInjectionArgument::service(getcwd().'/src/Resources/config/services.xml'))
            ->shouldBeCalled()
        ;

        $containerBuilderProphecy
            ->setDefinition(
                'loopback_api.resource',
                DependencyInjectionArgument::definition(Resource::class)
            )
            ->shouldBeCalled()
        ;

        $containerBuilderProphecy
            ->setDefinition(
                'loopback_api.http.query_extractor.order_filter_query_extractor',
                DependencyInjectionArgument::definition('Fidry\LoopBackApiBundle\Http\Request\FilterQueryExtractor')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'loopback_api.http.query_extractor.where_filter_query_extractor',
                DependencyInjectionArgument::definition('Fidry\LoopBackApiBundle\Http\Request\FilterQueryExtractor')
            )
            ->shouldBeCalled()
        ;


        $containerBuilderProphecy
            ->setDefinition(
                'loopback_api.filter.where_filter',
                DependencyInjectionArgument::definition('Fidry\LoopBackApiBundle\Filter\WhereFilter')
            )
            ->shouldBeCalled()
        ;
        $containerBuilderProphecy
            ->setDefinition(
                'loopback_api.filter.order_filter',
                DependencyInjectionArgument::definition('Fidry\LoopBackApiBundle\Filter\OrderFilter')
            )
            ->shouldBeCalled()
        ;

        return $containerBuilderProphecy;
    }
}
