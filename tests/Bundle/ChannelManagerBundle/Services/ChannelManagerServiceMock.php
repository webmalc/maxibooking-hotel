<?php

namespace Tests\Bundle\ChannelManagerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\ChannelManagerBundle\Document\BookingConfig;
use MBH\Bundle\ChannelManagerBundle\Document\MyallocatorConfig;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class ChannelManagerServiceMock
{
    const FIRST_ROOM_ID = 'ID1';
    const FIRST_ROOM_NAME = 'Room name';
    const SECOND_ROOM_ID = 'ID2';
    const SECOND_ROOM_NAME = 'Room name 2';
    const FIRST_TARIFF_ID = 'ID1';
    const FIRST_TARIFF_NAME = 'Tariff 1';

    const UNAVAILABLE_PRICES = [
        'isPersonPrice' => null,
        'additionalChildrenPrice' => null,
        'additionalPrice' => null,
    ];

    const UNAVAILABLE_PRICES_ADAPTER = [
        'isPersonPrice' => 'isSinglePlacement',
        'additionalChildrenPrice' => 'isChildPrices',
        'additionalPrice' => 'isIndividualAdditionalPrices',
    ];

    const UNAVAILABLE_RESTRICTIONS = [
        'minBeforeArrival' => null,
        'maxBeforeArrival' => null,
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DocumentManager $dm
     */
    private $dm;

    /**
     * Current selected hotel
     * @var \MBH\Bundle\HotelBundle\Document\Hotel|null
     */
    protected $hotel;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $this->container->get('doctrine_mongodb')->getManager();
        $this->hotel = $this->container->get('mbh.hotel.selector')->getSelected();
    }

    /**
     * Pull rooms from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullRooms(ChannelManagerConfigInterface $config)
    {

        return [
            self::FIRST_ROOM_ID => self::FIRST_ROOM_NAME,
            self::SECOND_ROOM_ID => self::SECOND_ROOM_NAME
        ];
    }

    /**
     * Pull tariffs from service server
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function pullTariffs(ChannelManagerConfigInterface $config)
    {
        return [
            'ID1' => [
                'title' => self::FIRST_TARIFF_NAME,
                'readonly' => false,
                'is_child_rate' => false,
                'rooms' => [
                    self::FIRST_ROOM_ID => self::FIRST_ROOM_NAME,
                    self::SECOND_ROOM_ID => self::SECOND_ROOM_NAME
                ]
            ]
        ];
    }

    public function sendTestRequestAndGetErrorMessage(ChannelManagerConfigInterface $config)
    {
        return null;
    }

    public function safeConfigDataAndGetErrorMessage()
    {
        return '';
    }

    public function associateUser($username, $password)
    {
        return 'some-token';
    }

    public function roomList(MyallocatorConfig $config, $grouped = false)
    {
        if ($grouped) {
            return $this->pullRooms($config);
        }

        return [
            ['Disabled' => false, 'RoomId' => self::FIRST_ROOM_ID],
            ['Disabled' => false, 'RoomId' => self::SECOND_ROOM_ID],
        ];
    }

    public function propertyList(MyallocatorConfig $config)
    {
        return [
            ['id' => self::FIRST_ROOM_ID, 'name' => 'First']
        ];
    }

    public function syncServices(ChannelManagerConfigInterface $config)
    {

    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    public function getNotifications(ChannelManagerConfigInterface $config): array
    {
        $errors = [];
        if (!$config->getIsEnabled()) {
            return [];
        }
        $trans = $this->container->get('translator');
        $getError = function (array $types, string $message, array &$errors, string $method) use ($config, $trans) {
            if (empty($types)) {
                return $errors;
            }
            if (count($types) && count($forbiddenPrices = $this->$method($config, $types))) {
                $error = $trans->trans($message) . ': ';
                $error .= implode(', ', array_map(function ($element) use ($trans, $message) {
                    return $trans->trans($message . '.type.' . $element);
                }, $forbiddenPrices));
                $errors[] = $error;
            }

            return $errors;
        };
        $getError(static::UNAVAILABLE_PRICES, 'channelmanager.notifications.prices', $errors, 'getForbiddenPrices');
//        $getError(static::UNAVAILABLE_RESTRICTIONS, 'channelmanager.notifications.restrictions', $errors, 'countRestrictions');

        return $errors;
    }

    public function getBookingAccountConfirmationCode(BookingConfig $config)
    {
        return 200;
    }

    public function getChannelManagerConfig()
    {
        return $config = $this->container->get('mbh.hotel.selector')->getSelected()->getBookingConfig();
    }

    protected function getForbiddenPrices(ChannelManagerConfigInterface $config, array $types = []): array
    {
        $builder = $this->getPricesByConfigQueryBuilder($config);
        $forbiddenPricesArray = [];
        $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['hotel.id' => $this->hotel->getId()]);

        foreach ($roomTypes as $roomType) {
            foreach ($types as $type => $val) {
                if (in_array($type, $forbiddenPricesArray)) {
                    continue;
                }
                $paramName = 'get' . ucfirst(self::UNAVAILABLE_PRICES_ADAPTER[$type]);
                if ($roomType->$paramName() && $roomType->$paramName() !== $types[$type]) {
                    $builderMock = clone $builder;
                    $builderMock->field($type)->notEqual($val);
                    if ($builderMock->getQuery()->count()) {
                        $forbiddenPricesArray[] = $type;
                    }
                }
            }
        }

        return $forbiddenPricesArray;
    }

    /**
     * Get priceCaches by config
     *
     * @param ChannelManagerConfigInterface $config
     * @return Builder
     */
    protected function getPricesByConfigQueryBuilder(ChannelManagerConfigInterface $config): Builder
    {
        $builder = $this->dm->getRepository('MBHPriceBundle:PriceCache')->createQueryBuilder();

        $tariffsIds = $this->getTariffIdsFromConfig($config);
        $roomTypeIds = $this->getRoomTypeIdsFromConfig($config);

        $builder->field('tariff.id')->in($tariffsIds)
            ->field('roomType.id')->in($roomTypeIds)
            ->field('cancelDate')->equals(null)
            ->field('date')->gte(new \DateTime('midnight'));

        return $builder;
    }

    /**
     * Get tariffIds from config
     *
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    private function getTariffIdsFromConfig(ChannelManagerConfigInterface $config): array
    {
        return array_unique(array_map(function ($element) {
            return $element->getTariff()->getId();
        }, $config->getTariffs()->toArray()));
    }

    /**
     * Get roomTypeIds from config
     *
     * @param ChannelManagerConfigInterface $config
     * @return array
     */
    private function getRoomTypeIdsFromConfig(ChannelManagerConfigInterface $config): array
    {
        return array_unique(array_map(function ($element) {
            return $element->getRoomType()->getId();
        }, $config->getRooms()->toArray()));
    }

}