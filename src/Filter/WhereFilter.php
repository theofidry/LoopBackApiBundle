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

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Dunglas\ApiBundle\Doctrine\Orm\Filter\FilterInterface;
use Fidry\LoopBackApiBundle\Http\Request\FilterQueryExtractorInterface;
use Fidry\LoopBackApiBundle\Normalizer\ParameterValueNormalizer;
use Fidry\LoopBackApiBundle\Property\Property;
use Fidry\LoopBackApiBundle\Property\PropertyBag;
use Symfony\Component\HttpFoundation\RequestStack;

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

    /**
     * @var FilterQueryExtractorInterface
     */
    private $queryExtractor;

    /**
     * @var ParameterValueNormalizer
     */
    private $parameterValueNormalizer;

    /**
     * @var array|null
     */
    private $properties;

    /**
     * @var PropertyBag
     */
    private $propertyBag;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack                  $requestStack
     * @param ParameterValueNormalizer      $parameterValueNormalizer
     * @param FilterQueryExtractorInterface $queryExtractor
     * @param PropertyBag                   $propertyBag
     * @param null|array                    $properties Null to allow filtering on all properties with the exact strategy or a map of property name with strategy.
     */
    public function __construct(
        RequestStack $requestStack,
        ParameterValueNormalizer $parameterValueNormalizer,
        FilterQueryExtractorInterface $queryExtractor,
        PropertyBag $propertyBag,
        array $properties = null
    ) {
        $this->queryExtractor = $queryExtractor;
        $this->parameterValueNormalizer = $parameterValueNormalizer;
        $this->properties = $properties;
        $this->propertyBag = $propertyBag;
        $this->requestStack = $requestStack;
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
        $queryExpr = [];
        $aliases = [];

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
                // In this case, $key is the property "fullname", e.g. 'relatedDummy_user_name'
                $queryExpr = array_merge(
                    $queryExpr,
                    $this->handleFilter($queryBuilder, $key, $value)
                );
            }
        }

        foreach ($queryExpr as $expr) {
            $queryBuilder->andWhere($expr);
        }

        //TODO: do the joins
    }

    /**
     * Handles the given filter to call the proper operator. At this point, it's unclear if the value passed is the real
     * value operator.
     *
     * @param QueryBuilder    $queryBuilder
     * @param string          $property Complete property, ex: 'id', 'relatedDummy_id', 'relatedDummy_user_name', etc.
     * @param array|string    $value
     * @param string|null     $parameter If is string is used to construct the parameter to avoid parameter conflicts.
     *
     * @return array
     */
    private function handleFilter(
        QueryBuilder $queryBuilder,
        $property,
        $value,
        $parameter = null
    ) {
        $queryExpr = [];
        $filterProperty = $this->propertyBag->getProperty($property, $value);

        if (true === $filterProperty->getResourceMetadata()->hasField($filterProperty->getShortname())) {
            // Entity has the property
            if (true === is_array($value)) {
                foreach ($value as $operator => $operand) {
                    // Case where there is an operator
                    // $operand is the "real" $value
                    $queryExpr[] = $this->handleOperator(
                        $queryBuilder,
                        $filterProperty,
                        $operator,
                        $operand,
                        $parameter
                    );
                }
            } else {
                // Simple where
                $value = $this->parameterValueNormalizer->normalizeValue(
                    $filterProperty->getResourceMetadata(),
                    $filterProperty->getShortname(),
                    $value
                );
                if (null === $value) {
                    $queryExpr[] = $queryBuilder->expr()->isNull(sprintf(
                        '%s.%s',
                        $filterProperty->getQueryBuilderAlias(),
                        $property)
                    );
                } else {
                    if (null === $parameter) {
                        $parameter = $property;
                    }
                    $queryExpr[] = $queryBuilder->expr()->eq(
                        sprintf('%s.%s', $filterProperty->getQueryBuilderAlias(), $property),
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
     * @param QueryBuilder $queryBuilder
     * @param Property     $filterProperty
     * @param string       $operator
     * @param mixed        $value
     * @param string|null  $parameter If is string is used to construct the parameter to avoid parameter conflicts.
     *
     * @return Expr|null
     */
    private function handleOperator(
        QueryBuilder $queryBuilder,
        Property $filterProperty,
        $operator,
        $value,
        $parameter = null
    ) {
        $queryExpr = null;
        if (null === $parameter) {
            $parameter = $filterProperty->getShortname();
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
                sprintf('%s.%s', $filterProperty->getQueryBuilderAlias(), $filterProperty->getShortname()),
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
        $value = $this->parameterValueNormalizer->normalizeValue(
            $filterProperty->getResourceMetadata(),
            $filterProperty->getShortname(),
            $value
        );
        $parameterValue = (self::PARAMETER_OPERATOR_LIKE === $operator || self::PARAMETER_OPERATOR_NLIKE === $operator)
            ? sprintf('%%%s%%', $value)
            : $value
        ;

        $expressionPropertyArgument = sprintf(
            '%s.%s',
            $filterProperty->getQueryBuilderAlias(),
            $filterProperty->getShortname()
        );
        $expressionValueArgument = sprintf(':%s', $parameter);
        switch ($operator) {
            case self::PARAMETER_OPERATOR_GT:
                $queryExpr = $queryBuilder->expr()->gt($expressionPropertyArgument, $expressionValueArgument);
                break;

            case self::PARAMETER_OPERATOR_GTE:
                $queryExpr = $queryBuilder->expr()->gte($expressionPropertyArgument, $expressionValueArgument);
                break;

            case self::PARAMETER_OPERATOR_LT:
                $queryExpr = $queryBuilder->expr()->lt($expressionPropertyArgument, $expressionValueArgument);
                break;

            case self::PARAMETER_OPERATOR_LTE:
                $queryExpr = $queryBuilder->expr()->lte($expressionPropertyArgument, $expressionValueArgument);
                break;

            case self::PARAMETER_OPERATOR_NEQ:
                if (null === $value) {
                    // Skip the set parameter that takes place after the switch case
                    return $queryBuilder->expr()->isNotNull($expressionPropertyArgument);
                }

                $queryExpr = $queryBuilder->expr()->neq($expressionPropertyArgument, $expressionValueArgument);
                break;

            case self::PARAMETER_OPERATOR_LIKE:
                $queryExpr = $queryBuilder->expr()->like($expressionPropertyArgument, $expressionValueArgument);
                break;

            case self::PARAMETER_OPERATOR_NLIKE:
                $queryExpr = $queryBuilder->expr()->notLike($expressionPropertyArgument, $expressionValueArgument);
                break;
        }

        if (null === $queryBuilder->getParameter($parameter)) {
            $queryBuilder->setParameter($parameter, $parameterValue);
        }

        return $queryExpr;
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
}
