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

use Dunglas\ApiBundle\Api\ResourceInterface;
use Fidry\LoopBackApiBundle\Extractor\PropertyExtractor;
use Fidry\LoopBackApiBundle\Property\Property;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PropertyResolver
{
    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @var MetadataResolver
     */
    private $metadataResolver;

    /**
     * @var PropertyExtractor
     */
    private $propertyExtractor;

    public function __construct(
        PropertyExtractor $propertyExtractor,
        AliasResolver $aliasResolver,
        MetadataResolver $metadataResolver
    ) {
        $this->aliasResolver = $aliasResolver;
        $this->metadataResolver = $metadataResolver;
        $this->propertyExtractor = $propertyExtractor;
    }

    /**
     * @example
     *  relatedDummy_name
     *  relatedDymmy_id
     *  relatedDummy_user_id
     *  relatedDummy_user_name
     *
     * @param ResourceInterface $resource
     * @param string            $property
     *
     * @return Property
     */
    public function resolve(ResourceInterface $resource, $property)
    {
        return new Property(
            $this->propertyExtractor->getResourceProperty($property),
            $property,
            $this->aliasResolver->getJoinAliasesChain($resource->getEntityClass(), $property),
            $this->metadataResolver->getResourceMetadataOfProperty($resource->getEntityClass(), $property)
        );
    }
}
