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
     * @param string $property
     *
     * @return Property
     */
    public function getProperty($property)
    {
        // Resolve property if not present in the parameter bag
        if (false === array_key_exists($property, $this->properties)) {
            $this->properties[$property] = $this->propertyResolver->resolve($property);
        }

        return $this->properties[$property];
    }
}
