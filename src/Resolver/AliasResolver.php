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

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Fidry\LoopBackApiBundle\Extractor\PropertyExtractor;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class AliasResolver
{
    /**
     * @var PropertyExtractor
     */
    private $propertyExtractor;

    /**
     * @var MetadataResolver
     */
    private $metadataResolver;

    public function __construct(MetadataResolver $metadataResolver, PropertyExtractor $propertyExtractor)
    {
        $this->metadataResolver = $metadataResolver;
        $this->propertyExtractor = $propertyExtractor;
    }

    /**
     * @var string[] All the aliases used for the query builder
     *
     * @example
     *  for $property = 'name'
     *  => []
     *
     *  for $property = 'relatedDummy_name'
     *  => [
     *      'o.relatedDummy' => 'RelatedDummyJoinAlias'
     *  ]
     *
     *  for $property = 'relatedDummy_anotherDummy_name'
     *  => [
     *      'o.relatedDummy' => 'RelatedDummyJoinAlias',
     *      'RelatedDummyJoinAlias.anotherDummy' => 'AnotherDummyJoinAlias',
     *  ]
     */

    /**
     * @param array $property
     */
    public function getJoinAliasesChain($resourceClass, $property)
    {
        //TODO

        $assocationsMetadatas = $this->metadataResolver->getAssociationsMetadataForProperty($resourceClass, $property);



        $explodedProperty = $this->propertyExtractor->getExplodedProperty($property);
        array_pop($explodedProperty);

        if (0 === count($explodedProperty)) {
            return [];
        }



        // Add alias for the other properties
        foreach ($explodedProperty as $_property) {
            /*
             * case1: $explodedProperty = []
             * case2: $explodedProperty = ['relatedDummy']
             * case3: $explodedProperty = ['relatedDummy', 'anotherDummy']
             * case4: $explodedProperty = ['relatedDummy', 'anotherDummy', 'thirdDummy']
             */
            if (true === isset($aliases[$_property])) {
                continue;
            }

            $this->getResourceAliasForProperty($aliases, $explodedProperty);
        }

        // Add alias for the property
        if (false === isset($aliases[$property])) {
            $alias = 'WhereFilter';
            if (0 < count($explodedProperty)) {
                $alias .= sprintf('_%s', implode('_', $explodedProperty));
            }
            $alias .= sprintf('_%sAlias', $property);

            $aliases[$property] = $alias;
        }

        return $aliases[$property];
    }

    /**
     * Gets the alias used for the entity to which the property belongs.
     *
     * @example
     *  $property was `name`
     *  $explodedProperty then is []
     *  $aliases is []
     *  => 'o'
     *
     *  $property was `relatedDummy_name`
     *  $explodedProperty then is ['relatedDummy']
     *  $alias is ['relatedDummy' => 'WhereFilter_relatedDummyAlias']
     *  => WhereFilter_relatedDummyAlias
     *
     *  $property was `relatedDummy_anotherDummy_name`
     *  $explodedProperty then is ['relatedDummy', 'anotherDummy']
     *  $alias is [
     *      'relatedDummy' => 'WhereFilter_relatedDummyAlias',
     *      'anotherDummy' => 'WhereFilter_relatedDummy_anotherDummyAlias'
     *  ]
     *  => WhereFilter_relatedDummy_anotherDummyAlias
     *
     * @param string[] $aliases Array containing all the properties for each an alias is used. The key is the
     *                          property and the value the actual alias.
     * @param string[] $explodedProperty
     *
     * @return string alias
     */
    private function getResourceAliasForProperty(array &$aliases, array $explodedProperty)
    {
        if (0 === count($explodedProperty)) {
            return 'o';
        }

        $property = array_pop($explodedProperty);

        // Add alias for the other properties
        foreach ($explodedProperty as $_property) {
            /*
             * case1: $explodedProperty = []
             * case2: $explodedProperty = ['relatedDummy']
             * case3: $explodedProperty = ['relatedDummy', 'anotherDummy']
             * case4: $explodedProperty = ['relatedDummy', 'anotherDummy', 'thirdDummy']
             */
            if (true === isset($aliases[$_property])) {
                continue;
            }

            $this->getResourceAliasForProperty($aliases, $explodedProperty);
        }

        // Add alias for the property
        if (false === isset($aliases[$property])) {
            $alias = 'WhereFilter';
            if (0 < count($explodedProperty)) {
                $alias .= sprintf('_%s', implode('_', $explodedProperty));
            }
            $alias .= sprintf('_%sAlias', $property);

            $aliases[$property] = $alias;
        }

        return $aliases[$property];
    }
}
