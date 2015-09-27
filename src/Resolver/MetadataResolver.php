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
     * @internal param ClassMetadata $resourceMetadata
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
     * Gets class metadata for the given class.
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    public function getClassMetadata($class)
    {
        return $this
            ->managerRegistry
            ->getManagerForClass($class)
            ->getClassMetadata($class)
        ;
    }

    /**
     * Gets class metadata for the given resource.
     *
     * @param ResourceInterface $resource
     *
     * @return ClassMetadata
     */
    public function getResourceClassMetadata(ResourceInterface $resource)
    {
        return $this->getClassMetadata($resource->getEntityClass());
    }
}
