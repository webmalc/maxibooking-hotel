<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;

class SerializerSettings
{
    const API_GROUP = 'api';
    const NESTED_GROUP = 'nested';

    const DATE_FORMAT = 'd.m.Y';
    const DATETIME_FORMAT = 'd.m.Y H:i';
    const TIME_FORMAT = 'H:i';

    const NORMALIZED_FIELDS_BY_GROUPS = [
        Package::class => [
            self::API_GROUP => [
                'id', 'numberWithPrefix', 'begin', 'end', 'roomType', 'adults', 'children', 'accommodations'
            ]
        ],
        RoomType::class => [
            self::API_GROUP => [
                'id',
                'isEnabled',
                'hotel',
                'fullTitle',
                'description',
                'places',
                'additionalPlaces',
                'isSmoking',
                'isHostel',
                'facilities',
                'roomSpace',
                'onlineImages',
            ]
        ],
        Hotel::class => [
            self::API_GROUP => [
                'id',
                'fullTitle',
                'isEnabled',
                'isDefault',
                'isHostel',
                'description',
                'facilities',
                'images',
                'file',
                'latitude',
                'longitude',
                'street',
                'house',
                'corpus',
                'flat',
                'zipCode',
                'contactInformation',
                'mapImage'
            ]
        ],
        Tariff::class => [
            self::API_GROUP => [
                'id',
                'fullTitle',
                'description',
                'hotel',
                'isEnabled',
                'isDefault',
                'isOnline',
            ]
        ],
        SearchResult::class => [
            self::API_GROUP => [
                'begin',
                'end',
                'adults',
                'children',
                'roomType',
                'tariff',
                'price',
                'priceWithoutPromotionDiscount',
                'prices',
                'packagePrices',
                'roomsCount',
                'nights'
            ]
        ]
    ];

    const EXTERNAL_FIELD_NAMES_BY_INTERNAL = [
        Hotel::class => [
            'file' => 'logo',
            'fullTitle' => 'title'
        ],
        RoomType::class => [
            'title' => 'internalTitle',
            'fullTitle' => 'title'
        ],
        Tariff::class => [
            'fullTitle' => 'title'
        ]
    ];

    const EXCLUDED_FIELDS_BY_NORMALIZATION = [
        SearchQuery::class => [
            'memcached', 'querySaveId', 'save'
        ]
    ];
}