<?php

/*
 * This file is part of the LoopBackApiBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\LoopBackApiBundle\Tests\Filter;

use Doctrine\Common\Persistence\ManagerRegistry;
use Fidry\LoopBackApiBundle\Filter\OrderFilter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrderFilterTest.
 *
 * @coversDefaultClass ApiBundle\Filter\OrderFilter
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class OrderFilterTest extends \PHPUnit_Framework_TestCase
{
//    /**
//     * @var ManagerRegistry
//     */
//    private $managerRegistry;
//
//    /**
//     * {@inheritdoc}
//     */
//    protected function setUp()
//    {
//        $this->managerRegistry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')->getMock();
//    }
//
//    /**
//     * @dataProvider requestProvider
//     * @covers       ::extractValues
//     *
//     * @param Request $request  Input request
//     * @param array   $expected Expected array extracted from the request for this filter.
//     */
//    public function testExtractValues(Request $request, array $expected)
//    {
//        $filter = new OrderFilter($this->managerRegistry, 'order');
//        $filter->initParameter('order');
//        $actual = $filter->extractValues($request);
//
//        $this->assertEquals($expected, $actual);
//    }
//
//    /**
//     * Provides requests from which the filter will be applied and the values used by the order filter.
//     *
//     * @return array
//     */
//    public function requestProvider()
//    {
//        return [
//            [
//                Request::create(
//                    '/api/dummies',
//                    'GET',
//                    [
//                        'filter' => [
//                            'order' => [
//                                'name'     => 'asc',
//                                'property' => 'orderValue'
//                            ]
//                        ]
//                    ]
//                ),
//                [
//                    'name'     => 'asc',
//                    'property' => 'orderValue'
//                ]
//            ]
//        ];
//    }
}
