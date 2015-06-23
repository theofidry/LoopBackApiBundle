<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\LoopBackApiBundle\Filter;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class FilterTrait.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
trait FilterTrait
{
    /**
     * @var string key of the filter query for this filter
     */
    protected $parameter;

    /**
     * Sets Parameter.
     *
     * @param string $parameter
     *
     * @return $this
     */
    public function initParameter($parameter)
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Extract the values from the query for this filter.
     *
     * @param Request $request
     *
     * @return array
     */
    protected function extractProperties(Request $request)
    {
        $filter = $request->query->get('filter', []);
        if (array_key_exists($this->parameter, $filter)) {
            return $filter[$this->parameter];
        }

        return [];
    }
}
