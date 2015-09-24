<?php

namespace Fidry\LoopBackApiBundle\Tests\Property;

use Dunglas\ApiBundle\Api\ResourceInterface;
use Fidry\LoopBackApiBundle\Property\Property;
use Fidry\LoopBackApiBundle\Property\PropertyBag;
use Fidry\LoopBackApiBundle\Resolver\PropertyResolver;
use Fidry\LoopBackApiBundle\Tests\Functional\Bundle\TestBundle\Entity\AnotherDummy;
use Fidry\LoopBackApiBundle\Tests\Functional\Bundle\TestBundle\Entity\Dummy;

/**
 * @coversDefaultClass Fidry\LoopBackApiBundle\Property\PropertyBag
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PropertyBagTest extends \PHPUnit_Framework_TestCase
{
    public function testGetProperty()
    {
        $expectedProperty = $this->prophesize(Property::class)->reveal();

        $dummyResourceProphecy = $this->prophesize(ResourceInterface::class);
        $dummyResourceProphecy->getEntityClass()->willReturn(Dummy::class);
        $dummyResource = $dummyResourceProphecy->reveal();

        $anotherDummyResourceProphecy = $this->prophesize(ResourceInterface::class);
        $anotherDummyResourceProphecy->getEntityClass()->willReturn(AnotherDummy::class);
        $anotherDummyResource = $anotherDummyResourceProphecy->reveal();

        $propertyResolverProphecy = $this->prophesize(PropertyResolver::class);
        $propertyResolverProphecy->resolve($dummyResource, 'name')->willReturn($expectedProperty);
        $propertyResolverProphecy->resolve($dummyResource, 'name')->shouldBeCalledTimes(1);

        $propertyResolverProphecy->resolve($anotherDummyResource, 'name')->willReturn(clone $expectedProperty);

        $propertyBag = new PropertyBag($propertyResolverProphecy->reveal());

        $property = $propertyBag->getProperty($dummyResource, 'name');
        $this->assertSame($expectedProperty, $property);

        // Cache check
        $property = $propertyBag->getProperty($dummyResource, 'name');
        $this->assertSame($expectedProperty, $property);

        // CHeck that the property is relative ot the resource
        $property = $propertyBag->getProperty($anotherDummyResource, 'name');
        $this->assertNotSame($expectedProperty, $property);
    }
}
