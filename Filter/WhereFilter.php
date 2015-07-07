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
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Dunglas\ApiBundle\Doctrine\Orm\Filter\AbstractFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class SearchFilter.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class WhereFilter extends AbstractFilter
{
    use FilterTrait;

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
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        IriConverterInterface $iriConverter,
        PropertyAccessorInterface $propertyAccessor,
        array $properties = null
    )
    {
        parent::__construct($managerRegistry, $properties);

        $this->iriConverter = $iriConverter;
        $this->propertyAccessor = $propertyAccessor;
        $this->properties = (null === $properties)? $properties: array_flip($properties);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ResourceInterface $resource, QueryBuilder $queryBuilder, Request $request)
    {
        $queryValues = $this->extractProperties($request);
        $metadata = $this->getClassMetadata($resource);
        $fieldNames = array_flip($metadata->getFieldNames());
        $queryExpr = [];

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
                                $fieldNames,
                                $property,
                                $dataSetElem[$property],
                                sprintf('or_%s%d%d', $property, $index, $count)
                            );

                            $queries = array_merge($queries, $expr);
                            $count++;
                        }

                        if (2 === count($queries)) {
                            $queryExpr[] = $queryBuilder->expr()->orX($queries[0], $queries[1]);
                        }
                    }
                }
            } else {
                $queryExpr = array_merge($queryExpr, $this->handleFilter($queryBuilder, $fieldNames, $key, $value));
            }
        }

        foreach ($queryExpr as $expr) {
            $queryBuilder->andWhere($expr);
        }

        return $queryBuilder;
    }

    /**
     * Handle the given filter. At this point, it's unclear if the value passed is the real value or an operator.
     *
     * @param QueryBuilder $queryBuilder
     * @param array        $fieldNames
     * @param string       $property
     * @param array|string $value
     * @param string|null  $parameter If is string is used to construct the parameter to avoid parameter conflicts.
     *
     * @return array
     */
    private function handleFilter(QueryBuilder $queryBuilder, array $fieldNames, $property, $value, $parameter = null)
    {
        $queryExpr = [];

        // $key is a property
        if (true === isset($fieldNames[$property])) {
            // Entity has the property
            if (is_array($value)) {
                foreach ($value as $operator => $operand) {
                    // Case where there is an operator
                    $queryExpr[] = $this->handleOperator($queryBuilder, $property, $operator, $operand, $parameter);
                }
            } else {
                // Simple where
                if (self::PARAMETER_ID_KEY === $property) {
                    $value = $this->getFilterValueFromUrl($value);
                }

                $value = $this->normalizeValue($property, $value);
                if (null === $value) {
                    $queryExpr[] = $queryBuilder->expr()->isNull(sprintf('o.%s', $property));
                } else {
                    if (null === $parameter) {
                        $parameter = $property;
                    }
                    $queryExpr[] = $queryBuilder->expr()->eq(sprintf('o.%s', $property), sprintf(':%s', $parameter));
                    $queryBuilder->setParameter($parameter, $value);
                }
            }
        } else {
            // TODO: handle association
        }

        return $queryExpr;
    }

    /**
     * Get the proper query expression for the set of data given.
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $property
     * @param string       $operator
     * @param string|array $value
     * @param string|null  $parameter If is string is used to construct the parameter to avoid parameter conflicts.
     *
     * @return Expr|null
     */
    private function handleOperator(QueryBuilder $queryBuilder, $property, $operator, $value, $parameter = null)
    {
        $queryExpr = null;
        if (null === $parameter) {
            $parameter = $property;
        }

        // Only particular case: the between operator
        if (self::PARAMETER_OPERATOR_BETWEEN === $operator
            && is_array($value)
            && 2 === count($value)
        ) {
            $value       = array_values($value);
            $paramBefore = sprintf(':between_before_%s', $parameter);
            $paramAfter  = sprintf(':between_after_%s', $parameter);

            $queryExpr = $queryBuilder->expr()->between(
                sprintf('o.%s', $property),
                $paramBefore,
                $paramAfter
            );
            $queryBuilder->setParameters([
                $paramBefore => $value[0],
                $paramAfter  => $value[1]
            ]);

            return $queryExpr;
        }

        // Expect $value to be a string
        if (false === is_string($value)) {
            return null;
        }

        // Normalize $value before using it
        $value = $this->normalizeValue($property, $value);
        $parameterValue = (self::PARAMETER_OPERATOR_LIKE === $operator || self::PARAMETER_OPERATOR_NLIKE === $operator)
            ? sprintf('%%%s%%', $value)
            : $value
        ;

        switch ($operator) {
            case self::PARAMETER_OPERATOR_GT:
                $queryExpr = $queryBuilder->expr()->gt(sprintf('o.%s', $property), sprintf(':%s', $parameter));
                break;

            case self::PARAMETER_OPERATOR_GTE:
                $queryExpr = $queryBuilder->expr()->gte(sprintf('o.%s', $property), sprintf(':%s', $parameter));
                break;

            case self::PARAMETER_OPERATOR_LT:
                $queryExpr = $queryBuilder->expr()->lt(sprintf('o.%s', $property), sprintf(':%s', $parameter));
                break;

            case self::PARAMETER_OPERATOR_LTE:
                $queryExpr = $queryBuilder->expr()->lte(sprintf('o.%s', $property), sprintf(':%s', $parameter));
                break;

            case self::PARAMETER_OPERATOR_NEQ:
                if (null === $value) {
                    // Skip the set parameter that takes place after the switch case
                    return $queryBuilder->expr()->isNotNull(sprintf('o.%s', $property));
                } else {
                    $queryExpr = $queryBuilder->expr()->neq(sprintf('o.%s', $property), sprintf(':%s', $parameter));
                }
                break;

            case self::PARAMETER_OPERATOR_LIKE:
                $queryExpr = $queryBuilder->expr()->like(sprintf('o.%s', $property), sprintf(':%s', $parameter));
                break;

            case self::PARAMETER_OPERATOR_NLIKE:
                $queryExpr = $queryBuilder->expr()->notLike(sprintf('o.%s', $property), sprintf(':%s', $parameter));
                break;
        }

        if (null === $queryBuilder->getParameter($parameter)) {
            $queryBuilder->setParameter($parameter, $parameterValue);
        }

        return $queryExpr;
    }
    
    /**
     * Normalize the value. If the key is an ID, get the real ID value. If is null, set the value to null. Otherwise
     * return unchanged value.
     *
     * @param string $property
     * @param string $value
     *
     * @return string|null
     */
    private function normalizeValue($property, $value)
    {
        if (self::PARAMETER_ID_KEY === $property) {
            return $this->getFilterValueFromUrl($value);
        }

        if (self::PARAMETER_NULL_VALUE === $value) {
            return null;
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
    private function getFilterValueFromUrl($value)
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
}
