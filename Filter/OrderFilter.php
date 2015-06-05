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

use Doctrine\ORM\QueryBuilder;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Dunglas\ApiBundle\Doctrine\Orm\Filter\OrderFilter as DunglasOrderFilter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrderFilter.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class OrderFilter extends DunglasOrderFilter
{
    use FilterTrait;

    /**
     * @var array
     */
    private $properties;

    /**
     * {@inheritdoc}
     */
    public function apply(ResourceInterface $resource, QueryBuilder $queryBuilder, Request $request)
    {
        $this->applyFilter($resource, $queryBuilder, $this->extractValues($request));
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
                    'type'     => 'string',
                    'required' => false,
                ];
            }
        }

        return $description;
    }
}
