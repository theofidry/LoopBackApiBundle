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

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Dunglas\ApiBundle\Doctrine\Orm\SearchFilter as DunglasSearchFilter;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class SearchFilter.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class WhereFilter extends DunglasSearchFilter
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
     * @var null|array
     */
    private $properties;

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
        parent::__construct($managerRegistry, $iriConverter, $propertyAccessor, $properties);

        $this->iriConverter = $iriConverter;
        $this->propertyAccessor = $propertyAccessor;
        $this->properties = (null === $properties)? $properties: array_flip($properties);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ResourceInterface $resource, QueryBuilder $queryBuilder, Request $request)
    {
        $this->applyFilter($resource, $queryBuilder, $this->extractValues($request));
    }

    /**
     * Create query to order the collection by the properties passed.
     *
     * @param ResourceInterface $resource
     * @param QueryBuilder      $queryBuilder
     * @param array|null        $queryValues
     *
     * @return QueryBuilder
     */
    public function applyFilter(ResourceInterface $resource, QueryBuilder $queryBuilder, array $queryValues = null)
    {
        $metadata = $this->getClassMetadata($resource);
        $fieldNames = array_flip($metadata->getFieldNames());
        $queryExpr = [];

        /*
         * TODO:
         *
         * Left to consider:
         * - empty values
         * - single/multiassociation values
         * - embedded relation associations
         */

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
                 *        'property' => [
                 *            'operator' => 'operand'
                 *         ],
                 *         'property' => value
                 *    ],
                 *    1 => [...],
                 *    ...
                 * ]
                 */
                foreach ($value as $dataSet) {
                    // Expect $dataSet to be an array containing 2 parameters
                    if (is_array($dataSet) && 2 === count($dataSet)) {
                        $dataSet = array_values($dataSet);
                        $queries = [];

                        // Handle each "query" of $dataSet
                        foreach ($dataSet as $property => $propValue) {
                            // At this point $propValue may be either a value or an array (for operators)
                            $expr = $this->handleFilter($queryBuilder, $fieldNames, $property, $propValue);
                            if (null !== $expr) {
                                $queries[] = $expr;
                            }
                        }

                        if (2 === count($queries)) {
                            $queryExpr[] = $queryBuilder->expr()->orX($queries[0], $queries[1]);
                        }
                    }
                }
            } else {
                $queryExpr[] = $this->handleFilter($queryBuilder, $fieldNames, $key, $value);
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
     * @param mixed        $value
     *
     * @return array
     */
    private function handleFilter(QueryBuilder $queryBuilder, array $fieldNames, $property, $value)
    {
        $queryExpr = [];

        // $key is a property
        if (isset($fieldNames[$property])) {
            // Entity has the property
            if (is_array($value)) {
                foreach ($value as $operator => $operand) {
                    // Case where there is an operator
                    // The `between` operator is handled separately
                    if (self::PARAMETER_OPERATOR_BETWEEN === $operator
                        && is_array($operand)
                        && 2 === count($operand)
                    ) {
                        $operand = array_values($operand);

                        $query1 = $this->handleOperator($property, $operator, $operand[0]);
                        $query2 = $this->handleOperator($property, $operator, $operand[1]);

                        if (null !== $query1 && null !== $query2) {
                            $queryExpr[] = $queryBuilder->expr()->andX($query1, $query2);
                        }
                    } else {
                        $queryExpr[] = $this->handleOperator($property, $operator, $operand);
                    }
                }
            } else {
                // Simple where
                if (self::PARAMETER_ID_KEY === $property) {
                    $value = $this->getFilterValueFromUrl($value);
                }
                // TODO $queryExpr[] = expr(prop = $value)
            }
        } else {
            // TODO: handle association
        }

        return $queryExpr;
    }

    /**
     * Get the proper query expression for the set of data given.
     *
     * @param string       $property
     * @param string       $operator
     * @param string|array $value
     *
     * @return Expr|null
     */
    private function handleOperator($property, $operator, $value)
    {
        // Expect $operand to be a value
        if (is_array($value)) {
            return null;
        }
        $value = $this->normalizeValue($property, $value);

        switch ($operator) {
            case self::PARAMETER_OPERATOR_GT:
                // TODO expr: prop > $operand
                break;

            case self::PARAMETER_OPERATOR_GTE:
                // TODO expr: prop >= $operand
                break;

            case self::PARAMETER_OPERATOR_LT:
                // TODO expr: prop < $operand
                break;

            case self::PARAMETER_OPERATOR_LTE:
                // TODO expr: prop <= $operand
                break;

            case self::PARAMETER_OPERATOR_NEQ:
                // TODO expr: prop != $operand
                break;

            case self::PARAMETER_OPERATOR_LIKE:
                // TODO expr: prop like $operand
                break;

            case self::PARAMETER_OPERATOR_NLIKE:
                // TODO expr: prop nlike $operand
                break;
        }

        // TODO: return $expr;
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
     * @param ClassMetadata $metadata
     * @param array         $fieldNames
     * @param QueryBuilder  $queryBuilder
     * @param string        $property
     * @param string        $value
     */
    private function applyFilterOnStringValue(
        ClassMetadata $metadata,
        array $fieldNames,
        QueryBuilder $queryBuilder,
        $property,
        $value
    ) {
        // Check if property is enabled
        if (null === $this->properties || isset($this->properties[$property])) {
            // Test if entity has property or if relation has entity
            if (isset($fieldNames[$property])) {
                // If is ID retrieve the real value
                if ('id' === $property) {
                    $value = $this->getFilterValueFromUrl($value);
                }

                if ('null' === $value) {
                    $queryBuilder->andWhere(sprintf('o.%s IS NULL', $property));
                } else {
                    $queryBuilder
                        ->andWhere(sprintf('o.%1$s = :%1$s', $property))
                        ->setParameter($property, $value);
                }
            } elseif ($metadata->isSingleValuedAssociation($property)
                || $metadata->isCollectionValuedAssociation($property)
            ) {
                $value = $this->getFilterValueFromUrl($value);

                $queryBuilder->join(sprintf('o.%s', $property), $property);
                if ('null' === $value) {
                    $queryBuilder->andWhere(sprintf('o.%s IS NULL', $property));
                } else {
                    $queryBuilder
                        ->andWhere(sprintf('o.%1$s = :%1$s', $property))
                        ->setParameter($property, $value);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(ResourceInterface $resource)
    {
        $description = [];
        $metadata = $this->getClassMetadata($resource);

        foreach ($metadata->getFieldNames() as $fieldName) {
            $found = isset($this->properties[$fieldName]);
            if ($found || null === $this->properties) {
                $description[sprintf('filter[%s][%s]', $this->parameter, $fieldName)] = [
                    'property' => $fieldName,
                    'type' => $metadata->getTypeOfField($fieldName),
                    'required' => false,
                    'strategy' => self::STRATEGY_EXACT,
                ];
            }
        }

        foreach ($metadata->getAssociationNames() as $associationName) {
            $description[sprintf('filter[%s][%s]', $this->parameter, $associationName)] = [
                'property' => $associationName,
                'type' => 'iri',
                'required' => false,
                'strategy' => self::STRATEGY_EXACT,
            ];
        }

        return $description;
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
