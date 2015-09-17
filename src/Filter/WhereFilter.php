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
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Dunglas\ApiBundle\Doctrine\Orm\Filter\FilterInterface;
use Fidry\LoopBackApiBundle\Http\Request\FilterQueryExtractorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class SearchFilter.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class WhereFilter implements FilterInterface
{
    const PARAMETER_OPERATOR_OR = 'or';
    const PARAMETER_OPERATOR_GT = 'gt';
    const PARAMETER_OPERATOR_GTE = 'gte';
    const PARAMETER_OPERATOR_LT = 'lt';
    const PARAMETER_OPERATOR_LTE = 'lte';
    const PARAMETER_OPERATOR_BETWEEN = 'between';
    const PARAMETER_OPERATOR_NEQ = 'neq';
    const PARAMETER_OPERATOR_LIKE = 'like';
    const PARAMETER_OPERATOR_NLIKE = 'nlike';

    const PARAMETER_ID_KEY = 'id';
    const PARAMETER_NULL_VALUE = 'null';

    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    /**
     * @var FilterQueryExtractorInterface
     */
    private $queryExtractor;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var array|null
     */
    private $properties;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param ManagerRegistry               $managerRegistry
     * @param RequestStack                  $requestStack
     * @param IriConverterInterface         $iriConverter
     * @param PropertyAccessorInterface     $propertyAccessor
     * @param FilterQueryExtractorInterface $queryExtractor
     * @param null|array                    $properties Null to allow filtering on all properties with the exact strategy or a map of property name with strategy.
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        RequestStack $requestStack,
        IriConverterInterface $iriConverter,
        PropertyAccessorInterface $propertyAccessor,
        FilterQueryExtractorInterface $queryExtractor,
        array $properties = null
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->iriConverter = $iriConverter;
        $this->propertyAccessor = $propertyAccessor;
        $this->queryExtractor = $queryExtractor;
        $this->requestStack = $requestStack;
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ResourceInterface $resource, QueryBuilder $queryBuilder)
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return null;
        }

        $queryValues = $this->queryExtractor->extractProperties($request);
        $metadata = $this->getClassMetadata($resource);
        $queryExpr = [];
        $aliases = [];
        $associationsMetadata = [];

        // Retrieve all doctrine query expressions
        foreach ($queryValues as $key => $value) {
            if (self::PARAMETER_OPERATOR_OR === $key && is_array($value)) {
                /*
                 * OR operator case
                 *
                 * At this point $dataSet is expected to equal to something like this:
                 *
                 * $value = [
                 *    0 => [
                 *       0 => [
                 *          'property' => [
                 *             'operator' => 'operand'
                 *          ]
                 *       ],
                 *       1 => [
                 *          'property' => value
                 *       ]
                 *    ],
                 *    1 => [...],
                 *    ...
                 * ]
                 */
                foreach ($value as $index => $dataSet) {
                    // Expect $dataSet to be an array containing 2 parameters
                    if (is_array($dataSet) && 2 === count($dataSet)) {
                        $queries = [];

                        // Handle each "query" of $dataSet
                        $count = 0;
                        foreach ($dataSet as $dataSetElem) {
                            if (false === is_array($dataSetElem)) {
                                continue;
                            }
                            $property = key($dataSetElem);

                            // At this point the value may be either a value or an array (for operators)
                            $expr = $this->handleFilter(
                                $queryBuilder,
                                $metadata,
                                $aliases,
                                $associationsMetadata,
                                $property,
                                $dataSetElem[$property],
                                sprintf('or_%s%d%d', $property, $index, $count)
                            );

                            $queries = array_merge($queries, $expr);
                            ++$count;
                        }

                        if (2 === count($queries)) {
                            $queryExpr[] = $queryBuilder->expr()->orX($queries[0], $queries[1]);
                        }
                    }
                }
            } else {
                $queryExpr = array_merge(
                    $queryExpr,
                    $this->handleFilter($queryBuilder, $metadata, $aliases, $associationsMetadata, $key, $value)
                );
            }
        }

        foreach ($queryExpr as $expr) {
            $queryBuilder->andWhere($expr);
        }
    }

    /**
     * Handles the given filter to call the proper operator. At this point, it's unclear if the value passed is the real
     * value operator.
     *
     * @param QueryBuilder    $queryBuilder
     * @param ClassMetadata   $resourceMetadata
     * @param string[]        $aliases
     * @param ClassMetadata[] $associationsMetadata
     * @param string          $property
     * @param array|string    $value
     * @param string|null     $parameter If is string is used to construct the parameter to avoid parameter conflicts.
     *
     * @return array
     */
    private function handleFilter(
        QueryBuilder $queryBuilder,
        ClassMetadata $resourceMetadata,
        array $aliases,
        array $associationsMetadata,
        $property,
        $value,
        $parameter = null
    ) {
        $queryExpr = [];

        /*
         * simple (case 1):
         * $property = name
         *
         * relation (case 2):
         * $property = relatedDummy_name
         * $property = relatedDymmy_id
         * $property = relatedDummy_user_id
         * $property = relatedDummy_user_name
         */
        if (false !== strpos($property, '.')) {
            $explodedProperty = explode('.', $property);
        } else {
            $explodedProperty = explode('_', $property);
        }
        // we are in case 2
        $property = array_pop($explodedProperty);
        $alias = $this->getResourceAliasForProperty($aliases, $explodedProperty);
        $aliasMetadata = $this->getAssociationMetadataForProperty(
            $resourceMetadata,
            $associationsMetadata,
            $explodedProperty
        );

        if (true === $aliasMetadata->hasField($property)) {
            // Entity has the property
            if (is_array($value)) {
                foreach ($value as $operator => $operand) {
                    // Case where there is an operator
                    $queryExpr[] = $this->handleOperator(
                        $queryBuilder,
                        $alias,
                        $aliasMetadata,
                        $property,
                        $operator,
                        $operand,
                        $parameter
                    );
                }
            } else {
                // Simple where
                $value = $this->normalizeValue($aliasMetadata, $property, $value);
                if (null === $value) {
                    $queryExpr[] = $queryBuilder->expr()->isNull(sprintf('%s.%s', $alias, $property));
                } else {
                    if (null === $parameter) {
                        $parameter = $property;
                    }
                    $queryExpr[] = $queryBuilder->expr()->eq(
                        sprintf('%s.%s', $alias, $property),
                        sprintf(':%s', $parameter)
                    );
                    $queryBuilder->setParameter($parameter, $value);
                }
            }
        }

        return $queryExpr;
    }

    /**
     * Gets the proper query expression for the set of data given.
     *
     * @param QueryBuilder  $queryBuilder
     * @param string        $alias     alias of the entity to which belongs the property
     * @param ClassMetadata $aliasMetadata
     * @param string        $property
     * @param string        $operator
     * @param string|array  $value
     * @param string|null   $parameter If is string is used to construct the parameter to avoid parameter conflicts.
     *
     * @return Expr|null
     */
    private function handleOperator(
        QueryBuilder $queryBuilder,
        $alias,
        ClassMetadata $aliasMetadata,
        $property,
        $operator,
        $value,
        $parameter =
        null
    ) {
        $queryExpr = null;
        if (null === $parameter) {
            $parameter = $property;
        }

        // Only particular case: the between operator
        if (self::PARAMETER_OPERATOR_BETWEEN === $operator
            && is_array($value)
            && 2 === count($value)
        ) {
            $value = array_values($value);
            $paramBefore = sprintf(':between_before_%s', $parameter);
            $paramAfter = sprintf(':between_after_%s', $parameter);

            $queryExpr = $queryBuilder->expr()->between(
                sprintf('%s.%s', $alias, $property),
                $paramBefore,
                $paramAfter
            );
            $queryBuilder->setParameters([
                $paramBefore => $value[0],
                $paramAfter  => $value[1],
            ]);

            return $queryExpr;
        }

        // Expect $value to be a string
        if (false === is_string($value)) {
            return null;
        }

        // Normalize $value before using it
        $value = $this->normalizeValue($aliasMetadata, $property, $value);
        $parameterValue = (self::PARAMETER_OPERATOR_LIKE === $operator || self::PARAMETER_OPERATOR_NLIKE === $operator)
            ? sprintf('%%%s%%', $value)
            : $value;

        switch ($operator) {
            case self::PARAMETER_OPERATOR_GT:
                $queryExpr = $queryBuilder->expr()->gt(sprintf('%s.%s', $alias, $property), sprintf(':%s', $parameter));
                break;

            case self::PARAMETER_OPERATOR_GTE:
                $queryExpr = $queryBuilder->expr()->gte(sprintf('%s.%s', $alias, $property), sprintf(':%s',
                    $parameter));
                break;

            case self::PARAMETER_OPERATOR_LT:
                $queryExpr = $queryBuilder->expr()->lt(sprintf('%s.%s', $alias, $property), sprintf(':%s', $parameter));
                break;

            case self::PARAMETER_OPERATOR_LTE:
                $queryExpr = $queryBuilder->expr()->lte(sprintf('%s.%s', $alias, $property), sprintf(':%s',
                    $parameter));
                break;

            case self::PARAMETER_OPERATOR_NEQ:
                if (null === $value) {
                    // Skip the set parameter that takes place after the switch case
                    return $queryBuilder->expr()->isNotNull(sprintf('%s.%s', $alias, $property));
                } else {
                    $queryExpr = $queryBuilder->expr()->neq(sprintf('%s.%s', $alias, $property), sprintf(':%s', $parameter));
                }
                break;

            case self::PARAMETER_OPERATOR_LIKE:
                $queryExpr = $queryBuilder->expr()->like(sprintf('%s.%s', $alias, $property), sprintf(':%s',
                    $parameter));
                break;

            case self::PARAMETER_OPERATOR_NLIKE:
                $queryExpr = $queryBuilder->expr()->notLike(sprintf('%s.%s', $alias, $property), sprintf(':%s', $parameter));
                break;
        }

        if (null === $queryBuilder->getParameter($parameter)) {
            $queryBuilder->setParameter($parameter, $parameterValue);
        }

        return $queryExpr;
    }

    /**
     * Normalizes the value. If the key is an ID, get the real ID value. If is null, set the value to null. Otherwise
     * return unchanged value.
     *
     * @param ClassMetadata $metadata
     * @param string        $property
     * @param string        $value
     *
     * @return null|string
     */
    private function normalizeValue(ClassMetadata $metadata, $property, $value)
    {
        if (self::PARAMETER_ID_KEY === $property) {
            return $this->getFilterValueFromUrl($value);
        }

        if (self::PARAMETER_NULL_VALUE === $value) {
            return null;
        }

        switch ($metadata->getTypeOfField($property)) {
            case 'boolean':
                return (bool) $value;

            case 'integer':
                return (int) $value;

            case 'float':
                return (float) $value;

            case 'datetime':
                // the input has the format `2015-04-28T02:23:50 00:00`, transform it to match the database format
                // `2015-04-28 02:23:50`
                return preg_replace('/(\d{4}(-\d{2}){2})T(\d{2}(:\d{2}){2}) \d{2}:\d{2}/', '$1 $3', $value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * TODO
     */
    public function getDescription(ResourceInterface $resource)
    {
        return [];
    }

    /**
     * Gets the ID from an URI or a raw ID.
     *
     * @param string $value
     *
     * @return string
     */
    protected function getFilterValueFromUrl($value)
    {
        try {
            if ($item = $this->iriConverter->getItemFromIri($value)) {
                return $this->propertyAccessor->getValue($item, 'id');
            }
        } catch (\InvalidArgumentException $e) {
            // Do nothing, return the raw value
        }

        return $value;
    }

    /**
     * Gets the alias used for the entity to which the property belongs.
     *
     * @example
     *  $property was `name`
     *  $explodedProperty then is []
     *  => 'o'
     *
     *  $property was `relatedDummy_name`
     *  $explodedProperty then is ['relatedDummy']
     *  => WhereFilter_relatedDummyAlias
     *
     *  $property was `relatedDummy_anotherDummy_name`
     *  $explodedProperty then is ['relatedDummy', 'anotherDummy']
     *  => WhereFilter_relatedDummy_anotherDummyAlias
     *
     * @param string[] $aliases Array containing all the properties for each an alias is used. The key is the
     *                          property and the value the actual alias.
     * @param string[] $explodedProperty
     *
     * @return string alias
     */
    private function getResourceAliasForProperty(array &$aliases, array $explodedProperty)
    {
        if (0 === count($explodedProperty)) {
            return 'o';
        }

        foreach ($explodedProperty as $property) {
            if (false === isset($aliases[$property])) {
                $aliases[$property] = sprintf('WhereFilter_%sAlias', implode('_', $explodedProperty));
            }
        }

        return $aliases[end($explodedProperty)];
    }

    /**
     * Gets the metadata to which belongs the property.
     *
     * @example
     *  $property was `name`
     *  $explodedProperty then is []
     *  => $resourceMetadata
     *
     *  $property was `relatedDummy_name`
     *  $explodedProperty then is ['relatedDummy']
     *  => metadata of relatedDummy
     *
     *  $property was `relatedDummy_anotherDummy_name`
     *  $explodedProperty then is ['relatedDummy', 'anotherDummy']
     *  => metadata of anotherDummy
     *
     * @param ClassMetadata   $resourceMetadata
     * @param ClassMetadata[] $associationsMetadata
     * @param array           $explodedProperty
     *
     * @return ClassMetadata
     */
    private function getAssociationMetadataForProperty(
        ClassMetadata $resourceMetadata,
        array &$associationsMetadata,
        array
        $explodedProperty
    ) {
        if (0 === count($explodedProperty)) {
            return $resourceMetadata;
        }

        $parentResourceMetadata = $resourceMetadata;
        foreach ($explodedProperty as $index => $property) {
            if (1 <= $index) {
                $parentResourceMetadata = $associationsMetadata[$explodedProperty[$index - 1]];
            }

            if (false === $parentResourceMetadata->hasAssociation($property)) {
                throw new \RuntimeException(sprintf(
                    'Class %s::%s is not an association.',
                    $parentResourceMetadata->getName
                    (),
                    $property)
                );
            }

            if (false === isset($associationsMetadata[$property])) {
                $associationsMetadata[$property] = $this->getMetadata(
                    $parentResourceMetadata->getAssociationTargetClass($property)
                );
            }
        }

        return $associationsMetadata[end($explodedProperty)];
    }

    /**
     * Gets class metadata for the given class.
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    private function getMetadata($class)
    {
        return $this
            ->managerRegistry
            ->getManagerForClass($class)
            ->getClassMetadata($class)
        ;
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
}
