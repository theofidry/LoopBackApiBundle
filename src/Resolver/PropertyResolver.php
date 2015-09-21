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

    public function __construct(AliasResolver $aliasResolver, MetadataResolver $metadataResolver)
    {
        $this->aliasResolver = $aliasResolver;
        $this->metadataResolver = $metadataResolver;
    }

    /**
     * @example
     *  relatedDummy_name
     *  relatedDymmy_id
     *  relatedDummy_user_id
     *  relatedDummy_user_name
     *
     * @param string $property
     *
     * @return Property
     */
    public function resolve($property)
    {
        //TODO
        //        if (false !== strpos($property, '.')) {
//            $explodedProperty = explode('.', $property);
//        } else {
//            $explodedProperty = explode('_', $property);
//        }
//        // we are in case 2
//        $property = array_pop($explodedProperty);
//        $alias = $this->getResourceAliasForProperty($aliases, $explodedProperty);
//        $aliasMetadata = $this->getAssociationMetadataForProperty(
//            $resourceMetadata,
//            $associationsMetadata,
//            $explodedProperty
//        );
    }
}
