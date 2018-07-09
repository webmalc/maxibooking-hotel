<?php

namespace MBH\Bundle\BaseBundle\Service;


use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class DocumentsComparer
{
    const FIELDS_TO_COMPARE_BY_DOC_NAMES = [
        RoomCache::class => ['leftRooms'],
        PriceCache::class => ['price',
            'isPersonPrice',
            'additionalPrice',
            'getAdditionalChildrenPrice',
            'getSinglePrice',
            'getChildPrice',]
    ];

    private $propertyAccessor;

    public function __construct(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function isEqualByDocName($first, $second, string $docName)
    {
        if (!isset(self::FIELDS_TO_COMPARE_BY_DOC_NAMES[$docName])) {
            throw new \InvalidArgumentException('Fields to compare for document "' . $docName . ' are not defined');
        }

        return $this->isEqualByFields($first, $second, self::FIELDS_TO_COMPARE_BY_DOC_NAMES[$docName]);
    }

    /**
     * @param $firstEntity
     * @param $secondEntity
     * @param $comparedFields
     * @return bool
     */
    public function isEqualByFields($firstEntity, $secondEntity, $comparedFields)
    {
        if (is_null($firstEntity) xor is_null($secondEntity)) {
            return false;
        } elseif (is_null($firstEntity) && is_null($secondEntity)) {
            return true;
        }

        $isEqual = true;
        foreach ($comparedFields as $comparedField) {
            if ($this->propertyAccessor->getValue($firstEntity, $comparedField)
                != $this->propertyAccessor->getValue($secondEntity, $comparedField)) {
                $isEqual = false;
            }
        }

        return $isEqual;
    }
}