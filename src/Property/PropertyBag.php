<?php

namespace Fidry\LoopBackApiBundle\Property;

use Fidry\LoopBackApiBundle\Resolver\AliasResolver;
use Fidry\LoopBackApiBundle\Resolver\MetadataResolver;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PropertyBag
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
     * @var Property[]
     */
    private $properties = [];

    public function __construct(AliasResolver $aliasResolver, MetadataResolver $metadataResolver)
    {
        $this->aliasResolver = $aliasResolver;
        $this->metadataResolver = $metadataResolver;
    }

    /**
     * Gets a property object via its name.
     *
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
    public function resolveProperty($property)
    {
        //TODO call PropertyResolver
        if (false === array_key_exists($property, $this->properties)) {
            $this->_resolveProperty($property);
        }

        return $this->properties[$property];
    }

    private function _resolveProperty($property)
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
