<?php

namespace MBH\Bundle\ApiBundle\Service;

use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;

/**
 * Temporary serializer
 * Class ApiSerializer
 * @package MBH\Bundle\ApiBundle\Service
 */
class ApiSerializer
{
    public function denormalizePackageCriteria(array $requestCriteria)
    {
        $criteria = new PackageQueryCriteria();

        if (isset($requestCriteria['isConfirmed'])) {
            $criteria->isConfirmed = $requestCriteria['isConfirmed'] === 'true';
        }

        return $criteria;
    }

    /**
     * @param Package $package
     * @return array
     */
    public function normalizePackage(Package $package)
    {
        $normalizedPackage = [
            'id' => $package->getId(),
            'numberWithPrefix' => $package->getNumberWithPrefix(),
            'status' => $package->getStatus(),
            'begin' => $package->getBegin()->format(ApiRequestManager::DATE_FORMAT),
            'end' => $package->getEnd()->format(ApiRequestManager::DATE_FORMAT),
            'roomType' => [
                'id' => $package->getRoomType()->getId(),
                'name' => $package->getRoomType()->getName(),
            ],
            'adults' => $package->getAdults(),
            'children' => $package->getChildren(),
            'accommodations' => array_map(function (PackageAccommodation $accommodation) {
                return [
                    'begin' => $accommodation->getBegin()->format(ApiRequestManager::DATE_FORMAT),
                    'end' => $accommodation->getEnd()->format(ApiRequestManager::DATE_FORMAT),
                    'roomName' => $accommodation->getRoom()->getName(),
                    'roomTypeName' => $accommodation->getRoomType()->getName()
                ];
            }, $package->getAccommodations()->toArray())
        ];

        if ($package->getPayer()) {
            $normalizedPackage['payer'] = [
                'id' => $package->getPayer()->getId(),
                'name' => $package->getPayer()->getName(),
                'phone' => $package->getPayer()->getPhone(),
                'email' => $package->getPayer()->getEmail()
            ];
        }

        return $normalizedPackage;
    }
}