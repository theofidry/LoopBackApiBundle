<?php

namespace Fidry\LoopBackApiBundle\Tests\Normalizer;

use Doctrine\ORM\Mapping\ClassMetadata;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Dunglas\ApiBundle\Exception\InvalidArgumentException;
use Fidry\LoopBackApiBundle\Normalizer\ParameterValueNormalizer;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @coversDefaultClass Fidry\LoopBackApiBundle\Normalizer\ParameterValueNormalizer
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ParameterValueNormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testNormalizeIdWithIdValueAndExistingItem()
    {
        $item = new \stdclass();

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getItemFromIri(1)->willReturn($item);

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getTypeOfField('id')->shouldNotBeCalled();

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue($item, 'id')->willReturn(1);

        $normalizer = new ParameterValueNormalizer($iriConverterProphecy->reveal(), $propertyAccessorProphecy->reveal());

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'id', 1);

        $this->assertEquals(1, $normalizedValue);
    }

    public function testNormalizeIdWithIdValueAndNonExistantItem()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getItemFromIri(1)->willReturn(null);

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getTypeOfField('id')->shouldNotBeCalled();

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue(Argument::any(), 'id')->shouldNotBeCalled();

        $normalizer = new ParameterValueNormalizer($iriConverterProphecy->reveal(), $propertyAccessorProphecy->reveal());

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'id', 1);

        $this->assertEquals(1, $normalizedValue);
    }

    public function testNormalizeIdWithValidIriValueAndExistingItem()
    {
        $item = new \stdclass();

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getItemFromIri('/dummy/1')->willReturn($item);

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getTypeOfField('id')->shouldNotBeCalled();

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue($item, 'id')->willReturn(1);

        $normalizer = new ParameterValueNormalizer($iriConverterProphecy->reveal(), $propertyAccessorProphecy->reveal());

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'id', '/dummy/1');

        $this->assertEquals(1, $normalizedValue);
    }

    public function testNormalizeIdWithValidIriValueAndNonExistantItem()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getItemFromIri('/dummy/1')->willReturn(null);

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getTypeOfField('id')->shouldNotBeCalled();

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue(Argument::any(), 'id')->shouldNotBeCalled();

        $normalizer = new ParameterValueNormalizer($iriConverterProphecy->reveal(), $propertyAccessorProphecy->reveal());

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'id', '/dummy/1');

        $this->assertEquals('/dummy/1', $normalizedValue);
    }

    public function testNormalizeIdWithInvalidIriValue()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getItemFromIri('dummy//1')->willThrow(new InvalidArgumentException());
        $iriConverterProphecy->getItemFromIri('null')->willThrow(new InvalidArgumentException());

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getTypeOfField('id')->shouldNotBeCalled();

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue(Argument::any(), 'id')->shouldNotBeCalled();

        $normalizer = new ParameterValueNormalizer($iriConverterProphecy->reveal(), $propertyAccessorProphecy->reveal());

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'id', 'dummy//1');
        $this->assertEquals('dummy//1', $normalizedValue);

        // Even the null value will be returned unchanged
        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'id', 'null');
        $this->assertEquals('null', $normalizedValue);
    }

    public function testNormalizeNullValue()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getItemFromIri(Argument::any())->shouldNotBeCalled();

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getTypeOfField('name')->shouldNotBeCalled();

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue(Argument::any(), 'id')->shouldNotBeCalled();

        $normalizer = new ParameterValueNormalizer($iriConverterProphecy->reveal(), $propertyAccessorProphecy->reveal());

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'name', 'null');

        $this->assertNull($normalizedValue);
    }

    public function testNormalizeBooleanValue()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getItemFromIri(Argument::any())->shouldNotBeCalled();

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getTypeOfField('enabled')->willReturn('boolean');

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue(Argument::any(), Argument::any())->shouldNotBeCalled();

        $normalizer = new ParameterValueNormalizer($iriConverterProphecy->reveal(), $propertyAccessorProphecy->reveal());

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'enabled', 1);
        $this->assertTrue($normalizedValue);

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'enabled', '1');
        $this->assertTrue($normalizedValue);

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'enabled', 'a string (eq to bool true)');
        $this->assertTrue($normalizedValue);

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'enabled', 'false');
        $this->assertTrue($normalizedValue);

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'enabled', 0);
        $this->assertFalse($normalizedValue);

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'enabled', '0');
        $this->assertFalse($normalizedValue);
    }

    /**
     * @dataProvider intergerValueProvider
     */
    public function testNormalizeIntegerValue($value, $expected)
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getItemFromIri(Argument::any())->shouldNotBeCalled();

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getTypeOfField('count')->willReturn('integer');

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue(Argument::any(), Argument::any())->shouldNotBeCalled();

        $normalizer = new ParameterValueNormalizer($iriConverterProphecy->reveal(), $propertyAccessorProphecy->reveal());

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'count', $value);
        $this->assertEquals($expected, $normalizedValue);
    }

    /**
     * @dataProvider floatValueProvider
     */
    public function testNormalizeFloatValue($value, $expected)
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getItemFromIri(Argument::any())->shouldNotBeCalled();

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getTypeOfField('price')->willReturn('float');

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue(Argument::any(), Argument::any())->shouldNotBeCalled();

        $normalizer = new ParameterValueNormalizer($iriConverterProphecy->reveal(), $propertyAccessorProphecy->reveal());

        $normalizedValue = $normalizer->normalizeValue($metadataProphecy->reveal(), 'price', $value);
        $this->assertEquals($expected, $normalizedValue);
    }

    public function testNormalizeDatetimeValue()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getItemFromIri(Argument::any())->shouldNotBeCalled();

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getTypeOfField('dateOfBirth')->willReturn('datetime');

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue(Argument::any(), Argument::any())->shouldNotBeCalled();

        $normalizer = new ParameterValueNormalizer($iriConverterProphecy->reveal(), $propertyAccessorProphecy->reveal());

        $normalizedValue = $normalizer->normalizeValue(
            $metadataProphecy->reveal(),
            'dateOfBirth',
            '2015-04-28T02:23:50 00:00'
        );
        $this->assertEquals('2015-04-28 02:23:50', $normalizedValue);

        $normalizedValue = $normalizer->normalizeValue(
            $metadataProphecy->reveal(),
            'dateOfBirth',
            '2015-04-28 02:23:50 00:00'
        );
        $this->assertEquals('2015-04-28 02:23:50 00:00', $normalizedValue);
    }

    public function testNormalizeNonSupportedTypeValue()
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverterProphecy->getItemFromIri(Argument::any())->shouldNotBeCalled();

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getTypeOfField('dummy')->willReturn('unknown');

        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $propertyAccessorProphecy->getValue(Argument::any(), Argument::any())->shouldNotBeCalled();

        $normalizer = new ParameterValueNormalizer($iriConverterProphecy->reveal(), $propertyAccessorProphecy->reveal());

        $normalizedValue = $normalizer->normalizeValue(
            $metadataProphecy->reveal(),
            'dummy',
            'something'
        );
        $this->assertEquals('something', $normalizedValue);
    }

    public function intergerValueProvider()
    {
        return [
            [
                10,
                10
            ],
            [
                '10',
                10
            ],
            [
                10.8,
                10
            ],
            [
                '10.8',
                10
            ],
            [
                'a string',
                0,
            ]
        ];
    }

    public function floatValueProvider()
    {
        return [
            [
                10,
                10.0
            ],
            [
                '10',
                10.0
            ],
            [
                10.8,
                10.8
            ],
            [
                '10.8',
                10.8
            ],
            [
                'a string',
                0.0,
            ]
        ];
    }
}
