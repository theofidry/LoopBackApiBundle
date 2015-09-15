<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\LoopBackApiBundle\Http\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FilterQueryExtractor implements FilterQueryExtractorInterface
{
    /**
     * @var string|null
     */
    private $filterEntryPoint;

    /**
     * @var string key of the filter query for this filter
     */
    private $filterParameter;

    /**
     * @param string      $filterParameter
     * @param string|null $filterEntryPoint
     */
    public function __construct($filterParameter, $filterEntryPoint = null)
    {
        $this->filterEntryPoint = $filterEntryPoint;
        $this->filterParameter = $filterParameter;
    }

    /**
     * {@inheritdoc}
     *
     * @example
     *  $filterParameter = null
     *  $filterEntryPoint = order
     *  query parameters = ?order[prop]=ASC
     *
     *  ::extractProperties($request)
     *  => [
     *      'prop' => 'ASC'
     *  ]
     *
     *
     *  $filterParameter = filter
     *  $filterEntryPoint = order
     *  query parameters = ?filter[order][prop]=ASC
     *
     *  ::extractProperties($request)
     *  => [
     *      'prop' => 'ASC'
     *  ]
     */
    public function extractProperties(Request $request)
    {
        if (null === $this->filterEntryPoint) {
            $filter = $request->query->all();
        } else {
            $filter = $request->query->get($this->filterEntryPoint, []);
        }

        if (array_key_exists($this->filterParameter, $filter)) {
            return $filter[$this->filterParameter];
        }

        return [];
    }
}
