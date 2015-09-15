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
use Dunglas\ApiBundle\Api\Resource;
use Fidry\LoopBackApiBundle\Filter\OrderFilter;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class OrderFilterTest.
 *
 * @coversDefaultClass Fidry\LoopBackApiBundle\Filter\OrderFilter
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class OrderFilterTest extends KernelTestCase
{
    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

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
        $this->markTestSkipped('tempo');
        $request = Request::create($url, 'GET');
        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getCurrentRequest()->willReturn($request);

        $filter = new OrderFilter($this->managerRegistry, $requestStack->reveal(), 'order', $properties);
        $filter->initParameter('order');
        $queryBuilder = $this->getQueryBuilder();

        $filter->apply($this->resource, $queryBuilder);
        $actual = strtolower($queryBuilder->getQuery()->getDQL());
        $expected = ('' === $expected) ?
            strtolower(sprintf('SELECT o FROM %s o', $this->entityClass)) :
            strtolower(sprintf('SELECT o FROM %s o ORDER BY %s', $this->entityClass, $expected));

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
     * @return array
     */
    public function filterProvider()
    {
        $return = [];

        // Classical order
        $return[] = [
            null,
            '/api/dummies?filter[order][name]=asc',
            'o.name asc',
        ];

        // Order on non existing property
        $return[] = [
            null,
            '/api/dummies?filter[order][unknown]=asc',
            '',
        ];

        // Order on allowed property
        $return[] = [
            ['name' => null],
            '/api/dummies?filter[order][name]=asc&filter[order][alias]=desc',
            'o.name asc',
        ];

        return $return;
    }
}
