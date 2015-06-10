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
        $expected = strtolower($expected);

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
        return [
            [
                null,
                '/api/dummies?filter[where][name]=test',
                "SELECT o FROM $this->entityClass o WHERE o.name = test"
            ],
        ];
    }
}
