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
     * @param string        $shortname
     * @param string        $fullname
     * @param string        $alias
     * @param ClassMetadata $resourceMetadata
     */
    function __construct($shortname, $fullname, $alias, ClassMetadata $resourceMetadata)
    {
        $this->shortname = $shortname;
        $this->fullname = $fullname;
        $this->queryBuilderAlias = $alias;
        $this->resourceMetadata = $resourceMetadata;
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
}
