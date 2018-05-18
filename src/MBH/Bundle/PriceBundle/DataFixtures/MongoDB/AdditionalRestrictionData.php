<?php

namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\Restriction;

/**
 * Class RestrictionData
 */
class AdditionalRestrictionData extends AbstractFixture implements OrderedFixtureInterface
{
    public const DATA = [
        'zero' => [
            'main-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                    'offsets' => [],
                    'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'downTariff-tariff' =>[
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
        ],
        'one' => [
            'main-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'downTariff-tariff' =>[
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
        ],
        'two' => [
            'main-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'downTariff-tariff' =>[
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
        ],
        'three' => [
            'main-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'downTariff-tariff' =>[
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
        ],
        'four' => [
            'main-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'downTariff-tariff' =>[
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
        ],
        'hostel' => [
            'main-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'downTariff-tariff' =>[
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
            'upTariff-tariff' => [
                'minStayArrival' => [
                    [
                        'offsets' => [3, 4, 5, 6, 7],
                        'value' => 5
                    ],
                    [
                        'offsets' => [8],
                        'value' => 7
                    ]

                ],
                'maxStayArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]

                ],
                'minStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxStay' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'minBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxBeforeArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ],
                ],
                'minGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'maxGuest' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnArrival' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closedOnDeparture' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],
                'closed' => [
                    [
                        'offsets' => [],
                        'value' => null
                    ]
                ],

            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $hotels = $manager->getRepository('MBHHotelBundle:Hotel')->findAll();
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight +1 month -1 day');
        $period = new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $end);
        $accessor = $this->container->get('property_accessor');
        foreach ($hotels as $hotelNumber => $hotel) {
            foreach (self::DATA as $roomTypeKey => $data) {
                /** @var RoomType $roomType */
                $roomType = $this->getReference($roomTypeKey . '/' . $hotelNumber);
                foreach ($data as $tariffKey => $restrictionData) {
                    /** @var Tariff $tariff */
                    $tariff = $this->getReference($tariffKey . '/' . $hotelNumber);
                    foreach ($period as $day) {
                        $restriction = new Restriction();
                        $restriction
                            ->setRoomType($roomType)
                            ->setHotel($hotel)
                            ->setTariff($tariff)
                            ->setDate($day);

                        foreach ($restrictionData as $restrictionName => $restrictionValues) {
                            $actualBeginOffset = (int)$day->diff($begin)->format('%d');

                            foreach ($restrictionValues as $resValue) {
                                $daysOffsets = $resValue['offsets'];
                                $value = $resValue['value'];
                                if (\in_array($actualBeginOffset, $daysOffsets, true)) {
                                    $accessor->setValue($restriction, $restrictionName, $value);
                                }
                            }


                        }
                        $manager->persist($restriction);
                    }
                }
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 610;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }
}
