<?php

namespace Fidry\LoopBackApiBundle\Tests\Property;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Fidry\LoopBackApiBundle\Property\Property;

/**
 * @coversDefaultClass Fidry\LoopBackApiBundle\Property\Property
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testProperty()
    {
        $resourceMetadata= $this->prophesize(ClassMetadata::class)->reveal();

        $property = new Property('name', 'relatedDummy_name', 'random', $resourceMetadata);

        $this->assertEquals('name', $property->getShortname());
        $this->assertEquals('relatedDummy_name', $property->getFullname());
        $this->assertEquals('random', $property->getQueryBuilderAlias());
        $this->assertEquals($resourceMetadata, $property->getResourceMetadata());
    }
}
