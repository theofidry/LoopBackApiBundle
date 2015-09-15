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
interface FilterQueryExtractorInterface
{
    /**
     * Extracts the values from the request query for the filters.
     *
     * @param Request $request
     *
     * @return array
     */
    public function extractProperties(Request $request);
}
