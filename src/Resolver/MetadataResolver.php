<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\LoopBackApiBundle\Resolver;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Fidry\LoopBackApiBundle\Extractor\PropertyExtractor;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MetadataResolver
{
    /**
     * @var ClassMetadata[]
     */
    private $associationsMetadata = [];

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var PropertyExtractor
     */
    private $propertyExtractor;

    public function __construct(ManagerRegistry $managerRegistry, PropertyExtractor $propertyExtractor)
    {
        $this->managerRegistry = $managerRegistry;
        $this->propertyExtractor = $propertyExtractor;
    }

    /**
     * Gets the metadata of the entity to which belongs the property.
     *
     * @example
     *  $property was `name`
     *  => $resourceMetadata
     *
     *  $property was `relatedDummy_name`
     *  => metadata of relatedDummy
     *
     *  $property was `relatedDummy_anotherDummy_name`
     *  => metadata of anotherDummy
     *
     * @param string $resourceClass FQCN
     * @param string $property
     *
     * @return ClassMetadata
     */
    public function getResourceMetadataOfProperty($resourceClass, $property)
    {
        $resourceMetadata = $this->getClassMetadata($resourceClass);

        $explodedProperty = $this->propertyExtractor->getExplodedProperty($property);
        array_pop($explodedProperty);
        // $explodedProperty now only contains "associations"

        if (0 === count($explodedProperty)) {
            return $resourceMetadata;
        }

        if (true === array_key_exists($resourceMetadata->getName(), $this->associationsMetadata)
            && true === array_key_exists($property, $this->associationsMetadata[$resourceMetadata->getName()])
        ) {
            return $this->associationsMetadata[$resourceMetadata->getName()];
        }

        end($explodedProperty);
        $lastKey = key($explodedProperty);
        $parentResourceMetadata = $resourceMetadata;
        /*
         * $explodedProperty = ['relatedDummy']
         * => metadata of relatedDummy
         *
         * $explodedProperty = [
         *  'relatedDummy',
         *  'anotherDummy',
         * ]
         * => metadata of anotherDummy
         */
        foreach ($explodedProperty as $index => $property) {
            if (false === $parentResourceMetadata->hasAssociation($property)) {
                throw new \UnexpectedValueException(
                    sprintf(
                        'Class %s::%s is not an association.',
                        $parentResourceMetadata->getName
                        (),
                        $property
                    )
                );
            }

            $parentResourceMetadata = $this->getClassMetadata(
                $parentResourceMetadata->getAssociationTargetClass($property)
            );

            if ($lastKey === $index) {
                return $parentResourceMetadata;
            }
        }

        throw new \RuntimeException('No class metadata found');
    }

    /**
     * Gets the metadata of the entity associations.
     *
     * @example
     *  $property was `name`
     *  => []
     *
     *  $property was `relatedDummy_name`
     *  => [
     *      'relatedDummy' => [
     *          'property' => 'relatedDummy'
     *          'metadata' => $metadataOfRelatedDummy
     *      ]
     *  ]
     *
     *  $property was `relatedDummy_anotherDummy_name`
     *  => [
     *      'relatedDummy' => $metadataOfRelatedDummy,
     *      'relatedDummy_anotherDummy' => $metadataOfAnotherDummy
     *  ]
     *  => metadata of anotherDummy
     *  => [
     *      'relatedDummy' => [
     *          'property' => 'relatedDummy'
     *          'metadata' => $metadataOfRelatedDummy
     *      ],
     *      'relatedDummy_anotherDummy' => [
     *          'property' => 'anotherDummy'
     *          'metadata' => $metadataOfAnotherDummy
     *      ]
     *  ]
     *
     * @param string $resourceClass FQCN
     * @param string $property
     *
     * @return ClassMetadata[]
     */
    public function getAssociationsMetadataForProperty($resourceClass, $property)
    {
        $explodedProperty = $this->propertyExtractor->getExplodedProperty($property);
        array_pop($explodedProperty);

        $propertySeparator = PropertyExtractor::$separators[0]; //TODO refactor that to use DI instead

        $associationsMetadata = [];
        foreach ($explodedProperty as $index => $property) {
            $associationProperty = sprintf('%s%s',
                implode(
                    $propertySeparator,
                    array_slice($explodedProperty, 0, $index + 1)
                ),
                $propertySeparator
            );
            $associationsMetadata[$associationProperty] = [
                'property' => $property,
                'metadata' => $this->getResourceMetadataOfProperty(
                    $resourceClass,
                    sprintf('%s%s', $associationProperty, 'someProperty')
                )
            ];
        }

        return $associationsMetadata;
    }

    /**
     * Gets class metadata for the given class.
     *
     * @param string $class
     *
     * @return ClassMetadata|null
     */
    public function getClassMetadata($class)
    {
        $classManager = $this
            ->managerRegistry
            ->getManagerForClass($class)
        ;

        if (null === $classManager) {
            return null;
        }

        return $classManager->getClassMetadata($class);
    }

    /**
     * Gets class metadata for the given resource.
     *
     * @param ResourceInterface $resource
     *
     * @return ClassMetadata|null
     */
    public function getResourceClassMetadata(ResourceInterface $resource)
    {
        return $this->getClassMetadata($resource->getEntityClass());
    }
}
