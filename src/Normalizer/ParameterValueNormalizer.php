<?php

namespace Fidry\LoopBackApiBundle\Normalizer;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ParameterValueNormalizer
{
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
     * @param IriConverterInterface     $iriConverter
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(IriConverterInterface $iriConverter, PropertyAccessorInterface $propertyAccessor)
    {
        $this->iriConverter = $iriConverter;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Normalizes the value. If the key is an ID, get the real ID value. If is null, set the value to null. Otherwise
     * return unchanged value.
     *
     * @param ClassMetadata $resourceMetadata Metadata of the resource to which belongs the property
     * @param string        $property         Resource property name
     * @param string        $value            Property value
     *
     * @return string|null
     */
    public function normalizeValue(ClassMetadata $resourceMetadata, $property, $value)
    {
        if (self::PARAMETER_ID_KEY === $property) {
            return $this->getFilterValueFromUrl($value);
        }

        if (self::PARAMETER_NULL_VALUE === $value) {
            return null;
        }

        switch ($resourceMetadata->getTypeOfField($property)) {
            case 'boolean':
                return (bool)$value;

            case 'integer':
                return (int)$value;

            case 'float':
                return (float)$value;

            case 'datetime':
                // the input has the format `2015-04-28T02:23:50 00:00`, transform it to match the database format
                // `2015-04-28 02:23:50`
                return preg_replace('/(\d{4}(-\d{2}){2})T(\d{2}(:\d{2}){2}) \d{2}:\d{2}/', '$1 $3', $value);
        }

        return $value;
    }

    /**
     * Gets the ID from an IRI or a raw ID. If the IRI is unmatched (invalid IRI, not found or something else), the
     * value is returned unchanged.
     *
     * @param string $value
     *
     * @return string
     */
    private function getFilterValueFromUrl($value)
    {
        try {
            $item = $this->iriConverter->getItemFromIri($value);

            if (null !== $item) {
                return $this->propertyAccessor->getValue($item, 'id');
            }
        } catch (\InvalidArgumentException $exception) {
            // Do nothing, return the raw value
        }

        return $value;
    }
}
