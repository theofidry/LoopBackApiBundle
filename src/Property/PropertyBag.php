<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\LoopBackApiBundle\Property;

use Dunglas\ApiBundle\Api\ResourceInterface;
use Fidry\LoopBackApiBundle\Resolver\PropertyResolver;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PropertyBag
{
    /**
     * @var Property[]
     */
    private $properties = [];

    /**
     * @var PropertyResolver
     */
    private $propertyResolver;

    public function __construct(PropertyResolver $propertyResolver)
    {
        $this->propertyResolver = $propertyResolver;
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
     * @param ResourceInterface $resource
     * @param string            $property
     *
     * @return Property
     */
    public function getProperty(ResourceInterface $resource, $property)
    {
        $propertyKey = sprintf('%s_%s', $resource->getEntityClass(), $property);

        // Resolve property if not present in the parameter bag
        if (false === array_key_exists($propertyKey, $this->properties)) {
            $this->properties[$propertyKey] = $this->propertyResolver->resolve($resource, $property);
        }

        return $this->properties[$propertyKey];
    }
}
