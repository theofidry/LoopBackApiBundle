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
use Doctrine\ORM\EntityRepository;
use Dunglas\ApiBundle\Api\IriConverter;
use Dunglas\ApiBundle\Api\Resource;
use Fidry\LoopBackApiBundle\Filter\WhereFilter;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class WhereFilter.
 *
 * @coversDefaultClass Fidry\LoopBackApiBundle\Filter\WhereFilter
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class WhereFilterTest extends KernelTestCase
{
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var IriConverter
     */
    private $iriConverter;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        self::bootKernel();
        $this->entityClass = 'Fidry\LoopBackApiBundle\Tests\Functional\Bundle\TestBundle\Entity\Dummy';
        $this->managerRegistry = self::$kernel->getContainer()->get('doctrine');
        $this->iriConverter = self::$kernel->getContainer()->get('api.iri_converter');
        $this->propertyAccessor = self::$kernel->getContainer()->get('property_accessor');
        $this->repository = DoctrineTestHelper::createTestEntityManager()->getRepository($this->entityClass);
        $this->resource = new Resource($this->entityClass);
    }

    /**
     * @dataProvider filterProvider
     * @covers       ::apply
     *
     * @param array|null $properties Properties on which the filter may be enabled.
     * @param string     $url        URL.
     * @param string     $expected   Expected DQL query.
     * @param array      $parameters Expected parameters of the DQL query.
     */
    public function testFilter($properties, $url, $expected, $parameters = [])
    {
        $request = Request::create($url, 'GET');
        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getCurrentRequest()->willReturn($request);

        $filter = new WhereFilter(
            $this->managerRegistry,
            $requestStack->reveal(),
            $this->iriConverter,
            $this->propertyAccessor,
            $properties
        );
        $filter->initParameter('where');
        $queryBuilder = $this->getQueryBuilder();

        $filter->apply($this->resource, $queryBuilder);
        $actual = strtolower($queryBuilder->getQuery()->getDQL());
        $expected = ('' === $expected)
            ? strtolower(sprintf('SELECT o FROM %s o', $this->entityClass))
            : strtolower(sprintf('SELECT o FROM %s o WHERE %s', $this->entityClass, $expected));

        $this->assertEquals($expected, $actual);
        foreach ($parameters as $parameter => $value) {
            $this->assertEquals($value, $queryBuilder->getParameter($parameter)->getValue());
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder for filters.
     */
    public function getQueryBuilder()
    {
        return $this->repository->createQueryBuilder('o');
    }

    /**
     * @return array
     */
    public function filterProvider()
    {
        $return = [];

        // Classical where
        $return[] = [
            null,
            '/api/dummies?filter[where][name]=test',
            'o.name = :name',
            [
                'name' => 'test',
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][unknown]=test',
            '',
            [],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[yolo][name]=test',
            '',
            [],
        ];

        // Classical where on boolean
        $return[] = [
            null,
            '/api/dummies?filter[where][isEnabled]=0',
            'o.isEnabled = :isEnabled',
            [
                'isEnabled' => 0,
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][isEnabled]=1',
            'o.isEnabled = :isEnabled',
            [
                'isEnabled' => 1,
            ],
        ];

        // Classical where on DateTime
        //TODO: enable this test
//        $return[] = [
//            null,
//            '/api/dummies?filter[where][isEnabled]=0',
//            'o.name = test'
//        ];
//        $return[] = [
//            null,
//            '/api/dummies?filter[where][isEnabled]=1',
//            'o.name = test'
//        ];

        // Null value / Not null
        $return[] = [
            null,
            '/api/dummies?filter[where][name]=null',
            'o.name IS NULL',
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][name][neq]=null',
            'o.name IS NOT NULL',
        ];

        // Empty string value
        $return[] = [
            null,
            '/api/dummies?filter[where][name]=',
            'o.name = :name',
            [
                'name' => '',
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][name][neq]=',
            'o.name <> :name',
            [
                'name' => '',
            ],
        ];

        // Empty integer value
        $return[] = [
            null,
            '/api/dummies?filter[where][price]=',
            'o.price = :price',
            [
                'price' => '',
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][price][neq]=',
            'o.price <> :price',
            [
                'price' => '',
            ],
        ];

        // Empty boolean value
        $return[] = [
            null,
            '/api/dummies?filter[where][isEnabled]=',
            'o.isEnabled = :isEnabled',
            [
                'isEnabled' => '',
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][isEnabled][neq]=',
            'o.isEnabled <> :isEnabled',
            [
                'isEnabled' => '',
            ],
        ];

        // gt(e)/lt(e) operator
        $return[] = [
            null,
            '/api/dummies?filter[where][price][gt]=40',
            'o.price > :price',
            [
                'price' => 40,
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][price][gte]=40',
            'o.price >= :price',
            [
                'price' => 40,
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][price][lt]=40',
            'o.price < :price',
            [
                'price' => 40,
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][price][lte]=40',
            'o.price <= :price',
            [
                'price' => 40,
            ],
        ];

        // Between operator
        $return[] = [
            null,
            '/api/dummies?filter[where][price][between][0]=0&filter[where][price][between][1]=7',
            'o.price BETWEEN :between_before_price AND :between_after_price',
            [
                'between_before_price' => 0,
                'between_after_price'  => 7,
            ],
        ];

        // (n)like operator
        $return[] = [
            null,
            '/api/dummies?filter[where][name][like]=test',
            'o.name LIKE :name',
            [
                'name' => '%test%',
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][name][nlike]=test',
            'o.name NOT LIKE :name',
            [
                'name' => '%test%',
            ],
        ];

//
        // Or operator
        $return[] = [
            null,
            '/api/dummies?filter[where][or][0][][name]=test&filter[where][or][0][][price]=20',
            'o.name = :or_name00 OR o.price = :or_price01',
            [
                'or_name00'  => 'test',
                'or_price01' => 20,
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][or][0][][name][neq]=null&filter[where][or][0][][price]=20',
            'o.name IS NOT NULL OR o.price = :or_price01',
            [
                'or_price01' => 20,
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][or][0][][name]=test&filter[where][or][0][][name]=pol&filter[where][or][1][][price]=20&filter[where][or][1][][name]=toto',
            '(o.name = :or_name00 OR o.name = :or_name01) AND (o.price = :or_price10 OR o.name = :or_name11)',
            [
                'or_name00'  => 'test',
                'or_name01'  => 'pol',
                'or_price10' => 20,
                'or_name11'  => 'toto',
            ],
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][or][0][name]=test&filter[where][or][0][][price]=20',
            '',
            [],
        ];

        //TODO: test the config

        return $return;
    }
}
