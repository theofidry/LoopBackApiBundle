<?php

namespace Fidry\LoopBackApiBundle\Property;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class Property
{
    /**
     * @var string
     *
     * @example
     *  'name' is the shortname for e.g. 'name', 'relatedDummy_name', ...
     */
    private $shortname;

    /**
     * @var string
     *
     * @example
     *  'name'
     *  'relatedDummy_name'
     */
    private $fullname;

    /**
     * @var string Alias used for the query builder
     */
    private $queryBuilderAlias;

    /**
     * @var ClassMetadata metadata of the resource to which belongs the property
     */
    private $resourceMetadata;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @param string        $shortname
     * @param string        $fullname
     * @param string        $alias
     * @param ClassMetadata $resourceMetadata
     */
    function __construct($shortname, $fullname, $alias, $resourceMetadata, $value)
    {
        $this->shortname = $shortname;
        $this->fullname = $fullname;
        $this->queryBuilderAlias = $alias;
        $this->resourceMetadata = $resourceMetadata;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getQueryBuilderAlias()
    {
        return $this->queryBuilderAlias;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * @return ClassMetadata
     */
    public function getResourceMetadata()
    {
        return $this->resourceMetadata;
    }

    /**
     * @return string
     */
    public function getShortname()
    {
        return $this->shortname;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
