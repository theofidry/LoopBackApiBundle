<?php

namespace Fidry\LoopBackApiBundle\Resolver;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Dunglas\ApiBundle\Api\ResourceInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MetadataResolver
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Gets the metadata of the entity to which belongs the property.
     *
     * @example
     *  $property was `name`
     *  $explodedProperty then is []
     *  => $resourceMetadata
     *
     *  $property was `relatedDummy_name`
     *  $explodedProperty then is ['relatedDummy']
     *  => metadata of relatedDummy
     *
     *  $property was `relatedDummy_anotherDummy_name`
     *  $explodedProperty then is ['relatedDummy', 'anotherDummy']
     *  => metadata of anotherDummy
     *
     * @param ClassMetadata   $resourceMetadata
     * @param ClassMetadata[] $associationsMetadata
     * @param array           $explodedProperty
     *
     * @return ClassMetadata
     */
    public function getAssociationMetadataForProperty(
        ClassMetadata $resourceMetadata,
        array &$associationsMetadata,
        array $explodedProperty
    ) {
        if (0 === count($explodedProperty)) {
            return $resourceMetadata;
        }

        $parentResourceMetadata = $resourceMetadata;
        foreach ($explodedProperty as $index => $property) {
            if (0 < $index) {
                $parentResourceMetadata = $associationsMetadata[$explodedProperty[$index - 1]];
            }

            if (false === $parentResourceMetadata->hasAssociation($property)) {
                throw new \RuntimeException(sprintf(
                        'Class %s::%s is not an association.',
                        $parentResourceMetadata->getName
                        (),
                        $property)
                );
            }

            if (false === isset($associationsMetadata[$property])) {
                $associationsMetadata[$property] = $this->getMetadata(
                    $parentResourceMetadata->getAssociationTargetClass($property)
                );
            }
        }

        return $associationsMetadata[end($explodedProperty)];
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
