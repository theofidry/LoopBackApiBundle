<?php

namespace Fidry\LoopBackApiBundle\Extractor;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PropertyExtractor
{
    /**
     * @var string[] Characters that can be used as separators for a property route.
     *
     * @example
     *  if '.' is a property separator, 'dummy.name' will be considered as "property 'name' of 'dummy' (implying
     *  dummy will be treated as an association later).
     */
    public static $separators = [
        '.',
        '_',
    ];

    /**
     * @param $property
     *
     * @return string[]
     */
    public function getExplodedProperty($property)
    {
        foreach (self::$separators as $separator) {
            if (false !== strpos($property, $separator)) {
                return explode($separator, $property);
            }
        }

        return [];
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
