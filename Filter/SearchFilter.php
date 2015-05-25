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
use Doctrine\ORM\QueryBuilder;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Dunglas\ApiBundle\Doctrine\Orm\SearchFilter;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class SearchFilter.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class SearchFilter extends SearchFilter
{
    use FilterTrait;

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

        if (null !== $queryValues) {
            foreach ($queryValues as $queryName => $queryValue) {
                if (is_string($queryValue)) {
                    $this->applyFilterOnStringValue($metadata, $fieldNames, $queryBuilder, $queryName, $queryValue);
                }
            }
        }

        return $queryBuilder;
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
