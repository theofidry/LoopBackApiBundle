<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\LoopBackApiBundle\Tests\Http\Request;

use Fidry\LoopBackApiBundle\Http\Request\FilterQueryExtractor;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass Fidry\LoopBackApiBundle\Http\RequestFilterQueryExtractor
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class FilterQueryExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::__construct
     */
    public function testConstructor()
    {
        new FilterQueryExtractor('order');
        new FilterQueryExtractor('order', null);
        new FilterQueryExtractor('order', 'filter');
    }

    /**
     * @cover ::extractProperties
     */
    public function testExtractPropertiesWithoutFilterEntryParameter()
    {
        $filterQueryExtractors = new FilterQueryExtractor('order');

        $request = new Request([
            'order' => [
                'name' => 'fidry'
            ],
            'where' => [
                'id' => 1
            ]
        ]);

        $properties = $filterQueryExtractors->extractProperties($request);
        $this->assertEquals(
            [
                'name' => 'fidry'
            ],
            $properties
        );

        $request = new Request([
            'where' => [
                'id' => 1
            ]
        ]);

        $properties = $filterQueryExtractors->extractProperties($request);
        $this->assertCount(0, $properties);

        $request = new Request();

        $properties = $filterQueryExtractors->extractProperties($request);
        $this->assertCount(0, $properties);
    }

    /**
     * @cover ::extractProperties
     */
    public function testExtractPropertiesWithFilterEntryParameter()
    {
        $filterQueryExtractors = new FilterQueryExtractor('order', 'filter');

        $request = new Request([
            'filter' => [
                'order' => [
                    'name' => 'fidry'
                ],
                'where' => [
                    'id' => 1
                ]
            ],
            'where' => [
                'id' => 2
            ]
        ]);

        $properties = $filterQueryExtractors->extractProperties($request);
        $this->assertEquals(
            [
                'name' => 'fidry'
            ],
            $properties
        );

        $request = new Request([
            'where' => [
                'id' => 1
            ]
        ]);

        $properties = $filterQueryExtractors->extractProperties($request);
        $this->assertCount(0, $properties);

        $request = new Request();

        $properties = $filterQueryExtractors->extractProperties($request);
        $this->assertCount(0, $properties);
    }
}
