<?php

namespace MBH\Bundle\ApiBundle\Service;

use MBH\Bundle\BaseBundle\Lib\Normalization\CustomFieldType;
use MBH\Bundle\BaseBundle\Lib\Normalization\SerializerSettings;
use MBH\Bundle\BaseBundle\Service\MBHSerializer;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;

/**
 * Temporary serializer
 * Class ApiSerializer
 * @package MBH\Bundle\ApiBundle\Service
 */
class ApiSerializer
{
    private $isPackageSpecialFieldInit = false;
    private $serializer;

    public function __construct(MBHSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $packages
     * @return array
     */
    public function normalizePackages(array $packages)
    {
        $this->initSpecialFields();

        return array_map(function (Package $package) {
            return $this->normalizePackage($package);
        }, $packages);
    }

    /**
     * @param Package $package
     * @return array
     * @throws \ReflectionException
     * @throws \MBH\Bundle\BaseBundle\Lib\Normalization\NormalizationException
     */
    public function normalizePackage(Package $package)
    {
        $this->initSpecialFields();
        $normalizedPackage = $this->serializer->normalizeByGroup($package, SerializerSettings::API_GROUP);
        $normalizedPackage['status'] = $package->getStatus();

        return $normalizedPackage;
    }

    private function initSpecialFields()
    {
        if (!$this->isPackageSpecialFieldInit) {
            $this->serializer->setSpecialFieldTypes(Package::class, [
                'roomType' => new CustomFieldType(function (RoomType $roomType) {
                    return [
                        'id' => $roomType->getId(),
                        'name' => $roomType->getName(),
                    ];
                }),
                'accommodations' => new CustomFieldType(function ($accommodations) {
                    return array_map(function (PackageAccommodation $accommodation) {
                        return [
                            'begin' => $accommodation->getBegin()->format(ApiRequestManager::DATE_FORMAT),
                            'end' => $accommodation->getEnd()->format(ApiRequestManager::DATE_FORMAT),
                            'roomName' => $accommodation->getRoom()->getName(),
                            'roomTypeName' => $accommodation->getRoomType()->getName()
                        ];
                    }, is_array($accommodations) ? $accommodations : iterator_to_array($accommodations));
                }),
                'payer' => new CustomFieldType(function ($payer) {
                    return [
                        'id' => $payer->getId(),
                        'name' => $payer->getName(),
                        'phone' => $payer->getPhone(),
                        'email' => $payer->getEmail()
                    ];
                })
            ]);
            $this->isPackageSpecialFieldInit = true;
        }
    }
}