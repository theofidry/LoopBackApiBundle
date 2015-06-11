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
        $this->entityClass = 'ApiBundle\Test\Entity\Dummy';
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
     */
    public function testFilter($properties, $url, $expected)
    {
        $request = Request::create($url, 'GET');
        $filter = new WhereFilter($this->managerRegistry, $this->iriConverter, $this->propertyAccessor, $properties);
        $filter->initParameter('where');
        $queryBuilder = $this->getQueryBuilder();

        $filter->apply($this->resource, $queryBuilder, $request);
        $actual   = strtolower($queryBuilder->getQuery()->getDQL());
        $expected = strtolower(sprintf('SELECT o FROM %s o WHERE %s', $this->entityClass, $expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder for filters.
     */
    public function getQueryBuilder()
    {
        return $this->repository->createQueryBuilder('o');
    }

    /**
     * Providers 3 parameters:
     *  - filter parameters.
     *  - properties to test. Keys are the property name. If the value is true, the filter should work on the property,
     *    otherwise not.
     *  - expected DQL query
     *
     * @return array
     */
    public function filterProvider()
    {
        $return = [];

        // Classical where
        $return[] = [
            null,
            '/api/dummies?filter[where][name]=test',
            'o.name = test'
        ];

        // Classical where on boolean
        $return[] = [
            null,
            '/api/dummies?filter[where][isEnabled]=0',
            'o.name = test'
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][isEnabled]=1',
            'o.name = test'
        ];

        // Classical where on DateTime
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
            'o.name IS NULL'
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][name][neq]=null',
            'o.name IS NOT NULL'
        ];

        // Empty string value
        $return[] = [
            null,
            '/api/dummies?filter[where][name]=',
            'o.name = ""'
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][name][neq]=',
            'o.name <> ""'
        ];

        // Empty integer value
        $return[] = [
            null,
            '/api/dummies?filter[where][price]=',
            'o.price = 0'
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][price][neq]=',
            'o.price <> 0'
        ];

        // Empty boolean value
        $return[] = [
            null,
            '/api/dummies?filter[where][isEnabled]=',
            'o.isEnabled = 0'
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][isEnabled][neq]=',
            'o.isEnabled <> 0'
        ];

        //TODO: or operator

        // gt(e)/lt(e)
        $return[] = [
            null,
            '/api/dummies?filter[where][price][gt]=40',
            'o.price > 40'
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][price][gte]=40',
            'o.price >= 40'
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][price][lt]=40',
            'o.price < 40'
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][price][lte]=40',
            'o.price <= 40'
        ];

        // Between filter
        $return[] = [
            null,
            '/api/dummies?filter[where][price][between][0]=0&filter[where][price][between][1]=7',
            'o.price BETWEEN 0 AND 7'
        ];

        // (n)like
        $return[] = [
            null,
            '/api/dummies?filter[where][name][like]=test',
            'o.name LIKE "%test%"'
        ];
        $return[] = [
            null,
            '/api/dummies?filter[where][name][nlike]=test',
            'o.name NOT LIKE "%test%"'
        ];

        return $return;
    }
}
