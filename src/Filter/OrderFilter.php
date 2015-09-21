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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Dunglas\ApiBundle\Doctrine\Orm\Filter\FilterInterface;
use Fidry\LoopBackApiBundle\Http\Request\FilterQueryExtractorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class OrderFilter.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class OrderFilter implements FilterInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var FilterQueryExtractorInterface
     */
    private $queryExtractor;

    /**
     * @var array|null
     */
    private $properties;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param ManagerRegistry               $managerRegistry
     * @param RequestStack                  $requestStack
     * @param FilterQueryExtractorInterface $queryExtractor
     * @param array                         $properties
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        RequestStack $requestStack,
        FilterQueryExtractorInterface $queryExtractor,
        array $properties = null
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->requestStack = $requestStack;
        $this->queryExtractor = $queryExtractor;
        $this->properties = $properties;
    }

    /**
     * @param ResourceInterface $resource
     * @param QueryBuilder      $queryBuilder
     */
    public function apply(ResourceInterface $resource, QueryBuilder $queryBuilder)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $properties = $this->queryExtractor->extractProperties($request);
        $fieldNames = array_flip($this->getClassMetadata($resource)->getFieldNames());

        foreach ($properties as $property => $order) {
            if (!$this->isPropertyEnabled($property) || !isset($fieldNames[$property])) {
                continue;
            } elseif ('' === $order && isset($this->properties[$property])) {
                $order = $this->properties[$property];
            }

            $order = strtoupper($order);
            if ('ASC' === $order || 'DESC' === $order) {
                $queryBuilder->addOrderBy(sprintf('o.%s', $property), $order);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(ResourceInterface $resource)
    {
        return [];
    }

    /**
     * Gets class metadata for the given resource.
     *
     * @param ResourceInterface $resource
     *
     * @return ClassMetadata
     */
    private function getClassMetadata(ResourceInterface $resource)
    {
        $entityClass = $resource->getEntityClass();

        return $this
            ->managerRegistry
            ->getManagerForClass($entityClass)
            ->getClassMetadata($entityClass)
        ;
    }

    /**
     * Is the given property enabled?
     *
     * @param string $property
     *
     * @return bool
     */
    protected function isPropertyEnabled($property)
    {
        return null === $this->properties || array_key_exists($property, $this->properties);
    }
}
