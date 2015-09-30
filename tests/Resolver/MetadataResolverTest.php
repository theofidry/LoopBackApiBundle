<?php

namespace Fidry\LoopBackApiBundle\Tests\Resolver;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Fidry\LoopBackApiBundle\Extractor\PropertyExtractor;
use Fidry\LoopBackApiBundle\Resolver\MetadataResolver;

/**
 * @coversDefaultClass Fidry\LoopBackApiBundle\ResolverMetadataResolver
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MetadataResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover getResourceMetadataOfProperty
     * @dataProvider propertyProvider
     */
    public function testGetResourceMetadataOfProperty(
        ManagerRegistry $managerRegistry,
        $resourceClass,
        $property,
        $expected
    ) {
        $metadataResolver = new MetadataResolver($managerRegistry, new PropertyExtractor());

        try {
            $this->assertEquals(
                $expected,
                $metadataResolver->getResourceMetadataOfProperty($resourceClass, $property)
            );
        } catch (\UnexpectedValueException $exception) {
            if (null !== $expected) {
                $this->fail('Unexpected exception');
            }
        }
    }

    /**
     * @cover getAssociationsMetadataForProperty
     * @dataProvider associationsProvider
     */
    public function testGetAssociationsMetadataForProperty(
        ManagerRegistry $managerRegistry,
        $resourceClass,
        $property,
        $expected
    ) {
        $metadataResolver = new MetadataResolver($managerRegistry, new PropertyExtractor());

        try {
            $this->assertEquals(
                $expected,
                $metadataResolver->getAssociationsMetadataForProperty($resourceClass, $property)
            );
        } catch (\UnexpectedValueException $exception) {
            if (null !== $expected) {
                $this->fail('Unexpected exception');
            }
        }
    }

    public function propertyProvider()
    {
        $data = [];

        $resourceMetadata = $this->prophesize(ClassMetadata::class)->reveal();

        $dummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $dummyManagerProphecy->getClassMetadata('DummyClass')->willReturn($resourceMetadata);

        $managerRegistryProphecy = $this->prophesize(ManagerRegistry::class);
        $managerRegistryProphecy
            ->getManagerForClass('DummyClass')
            ->willReturn($dummyManagerProphecy->reveal())
        ;

        $data[] = [
            $managerRegistryProphecy->reveal(),
            'DummyClass',
            'name',
            $resourceMetadata
        ];


        $relatedDummyMetadata = $this->prophesize(ClassMetadata::class)->reveal();

        $resourceMetadataProphecy = $this->prophesize(ClassMetadata::class);
        $resourceMetadataProphecy->getName()->willReturn('DummyClass');
        $resourceMetadataProphecy->hasAssociation('relatedDummy')->shouldBeCalled();
        $resourceMetadataProphecy->getAssociationTargetClass('relatedDummy')->willReturn('RelatedDummyClass');
        $resourceMetadata = $resourceMetadataProphecy->reveal();

        $dummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $dummyManagerProphecy->getClassMetadata('DummyClass')->willReturn($resourceMetadata);

        $relatedDummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $relatedDummyManagerProphecy->getClassMetadata('RelatedDummyClass')->willReturn($relatedDummyMetadata);

        $managerRegistryProphecy = $this->prophesize(ManagerRegistry::class);
        $managerRegistryProphecy
            ->getManagerForClass('DummyClass')
            ->willReturn($dummyManagerProphecy->reveal())
        ;
        $managerRegistryProphecy
            ->getManagerForClass('RelatedDummyClass')
            ->willReturn($relatedDummyManagerProphecy->reveal())
        ;

        $data[] = [
            $managerRegistryProphecy->reveal(),
            'DummyClass',
            'relatedDummy_name',
            $relatedDummyMetadata,
        ];


        $anotherDummyMetadata = $this->prophesize(ClassMetadata::class)->reveal();

        $resourceMetadataProphecy = $this->prophesize(ClassMetadata::class);
        $resourceMetadataProphecy->getName()->willReturn('DummyClass');
        $resourceMetadataProphecy->hasAssociation('relatedDummy')->shouldBeCalled();
        $resourceMetadataProphecy->getAssociationTargetClass('relatedDummy')->willReturn('RelatedDummyClass');
        $resourceMetadata = $resourceMetadataProphecy->reveal();

        $relatedDummyMetadataProphecy = $this->prophesize(ClassMetadata::class);
        $relatedDummyMetadataProphecy->getName()->willReturn('RelatedDummyClass');
        $relatedDummyMetadataProphecy->hasAssociation('anotherDummy')->shouldBeCalled();
        $relatedDummyMetadataProphecy->getAssociationTargetClass('anotherDummy')->willReturn('AnotherDummyClass');
        $relatedDummyMetadata = $relatedDummyMetadataProphecy->reveal();

        $dummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $dummyManagerProphecy->getClassMetadata('DummyClass')->willReturn($resourceMetadata);

        $relatedDummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $relatedDummyManagerProphecy->getClassMetadata('RelatedDummyClass')->willReturn($relatedDummyMetadata);

        $anotherDummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $anotherDummyManagerProphecy->getClassMetadata('AnotherDummyClass')->willReturn($anotherDummyMetadata);

        $managerRegistryProphecy = $this->prophesize(ManagerRegistry::class);
        $managerRegistryProphecy
            ->getManagerForClass('DummyClass')
            ->willReturn($dummyManagerProphecy->reveal())
        ;
        $managerRegistryProphecy
            ->getManagerForClass('RelatedDummyClass')
            ->willReturn($relatedDummyManagerProphecy->reveal())
        ;
        $managerRegistryProphecy
            ->getManagerForClass('AnotherDummyClass')
            ->willReturn($anotherDummyManagerProphecy->reveal())
        ;

        $data[] = [
            $managerRegistryProphecy->reveal(),
            'DummyClass',
            'relatedDummy_anotherDummy_name',
            $anotherDummyMetadata,
        ];

        return $data;
    }

    public function associationsProvider()
    {
        $data = [];

        $resourceMetadata = $this->prophesize(ClassMetadata::class)->reveal();

        $dummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $dummyManagerProphecy->getClassMetadata('DummyClass')->willReturn($resourceMetadata);

        $managerRegistryProphecy = $this->prophesize(ManagerRegistry::class);
        $managerRegistryProphecy
            ->getManagerForClass('DummyClass')
            ->willReturn($dummyManagerProphecy->reveal())
        ;

        $data[] = [
            $managerRegistryProphecy->reveal(),
            'DummyClass',
            'name',
            []
        ];


        $relatedDummyMetadata = $this->prophesize(ClassMetadata::class)->reveal();

        $resourceMetadataProphecy = $this->prophesize(ClassMetadata::class);
        $resourceMetadataProphecy->getName()->willReturn('DummyClass');
        $resourceMetadataProphecy->hasAssociation('relatedDummy')->shouldBeCalled();
        $resourceMetadataProphecy->getAssociationTargetClass('relatedDummy')->willReturn('RelatedDummyClass');
        $resourceMetadata = $resourceMetadataProphecy->reveal();

        $dummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $dummyManagerProphecy->getClassMetadata('DummyClass')->willReturn($resourceMetadata);

        $relatedDummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $relatedDummyManagerProphecy->getClassMetadata('RelatedDummyClass')->willReturn($relatedDummyMetadata);

        $managerRegistryProphecy = $this->prophesize(ManagerRegistry::class);
        $managerRegistryProphecy
            ->getManagerForClass('DummyClass')
            ->willReturn($dummyManagerProphecy->reveal())
        ;
        $managerRegistryProphecy
            ->getManagerForClass('RelatedDummyClass')
            ->willReturn($relatedDummyManagerProphecy->reveal())
        ;

        $data[] = [
            $managerRegistryProphecy->reveal(),
            'DummyClass',
            'relatedDummy_name',
            [
                'relatedDummy' => $relatedDummyMetadata,
            ]
        ];


        $anotherDummyMetadata = $this->prophesize(ClassMetadata::class)->reveal();

        $resourceMetadataProphecy = $this->prophesize(ClassMetadata::class);
        $resourceMetadataProphecy->getName()->willReturn('DummyClass');
        $resourceMetadataProphecy->hasAssociation('relatedDummy')->shouldBeCalled();
        $resourceMetadataProphecy->getAssociationTargetClass('relatedDummy')->willReturn('RelatedDummyClass');
        $resourceMetadata = $resourceMetadataProphecy->reveal();

        $relatedDummyMetadataProphecy = $this->prophesize(ClassMetadata::class);
        $relatedDummyMetadataProphecy->getName()->willReturn('RelatedDummyClass');
        $relatedDummyMetadataProphecy->hasAssociation('anotherDummy')->shouldBeCalled();
        $relatedDummyMetadataProphecy->getAssociationTargetClass('anotherDummy')->willReturn('AnotherDummyClass');
        $relatedDummyMetadata = $relatedDummyMetadataProphecy->reveal();

        $dummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $dummyManagerProphecy->getClassMetadata('DummyClass')->willReturn($resourceMetadata);

        $relatedDummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $relatedDummyManagerProphecy->getClassMetadata('RelatedDummyClass')->willReturn($relatedDummyMetadata);

        $anotherDummyManagerProphecy = $this->prophesize(ObjectManager::class);
        $anotherDummyManagerProphecy->getClassMetadata('AnotherDummyClass')->willReturn($anotherDummyMetadata);

        $managerRegistryProphecy = $this->prophesize(ManagerRegistry::class);
        $managerRegistryProphecy
            ->getManagerForClass('DummyClass')
            ->willReturn($dummyManagerProphecy->reveal())
        ;
        $managerRegistryProphecy
            ->getManagerForClass('RelatedDummyClass')
            ->willReturn($relatedDummyManagerProphecy->reveal())
        ;
        $managerRegistryProphecy
            ->getManagerForClass('AnotherDummyClass')
            ->willReturn($anotherDummyManagerProphecy->reveal())
        ;

        $data[] = [
            $managerRegistryProphecy->reveal(),
            'DummyClass',
            'relatedDummy_anotherDummy_name',
            [
                'relatedDummy' => $relatedDummyMetadata,
                'anotherDummy' => $anotherDummyMetadata,
            ],
        ];

        return $data;
    }
}
