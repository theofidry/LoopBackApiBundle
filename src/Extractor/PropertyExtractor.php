<?php

namespace Fidry\LoopBackApiBundle\Extractor;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PropertyExtractor
{
    /**
     * @param $property
     *
     * @return string[]
     */
    public function getExplodedProperty($property)
    {
        if (false !== strpos($property, '.')) {
            return explode('.', $property);
        }

        return explode('_', $property);
    }

    /**
     * Gets the property resource from the property full name.
     *
     * @example
     *  relatedDummy_name
     *  => name
     *
     *  relatedDymmy_id
     *  => id
     *
     *  relatedDummy_user_id
     *  => id
     *
     *  relatedDummy_user_name
     *  => name
     *
     * @param string $property
     *
     * @return string
     */
    public function getResourceProperty($property)
    {
        $explodedProperty = $this->getExplodedProperty($property);

        return array_pop($explodedProperty);
    }
}
