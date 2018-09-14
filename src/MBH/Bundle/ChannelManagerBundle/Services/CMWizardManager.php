<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\BaseBundle\Service\WarningsCompiler;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Form\IntroType;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class CMWizardManager
{
    private $fieldsManager;
    private $tokenStorage;
    private $billingApi;
    private $translator;
    private $warningsCompiler;
    private $helper;
    private $router;

    public function __construct(
        DocumentFieldsManager $fieldsManager,
        TokenStorage $tokenStorage,
        BillingApi $billingApi,
        TranslatorInterface $translator,
        WarningsCompiler $warningsCompiler,
        Helper $helper,
        Router $router
    ) {
        $this->fieldsManager = $fieldsManager;
        $this->tokenStorage = $tokenStorage;
        $this->billingApi = $billingApi;
        $this->translator = $translator;
        $this->warningsCompiler = $warningsCompiler;
        $this->helper = $helper;
        $this->router = $router;
    }

    const CHANNEL_MANAGERS_WITH_CONFIGURATION_BY_TECH_SUPPORT = [
        'hundred_one_hotels',
        'vashotel'
    ];
    const HOTEL_ADDRESS_FIELDS = [];

    /**
     * @param string $channelManagerName
     * @return string
     */
    public function getIntroForm(string $channelManagerName)
    {
        if (!$this->isConfiguredByTechSupport($channelManagerName)) {
            throw new \InvalidArgumentException($channelManagerName . ' is configured by tech support!');
        }

        return IntroType::class;
    }


    /**
     * @param ChannelManagerConfigInterface|null $config
     * @param string $channelManagerName
     * @return bool|string
     */
    public function checkForReadinessOrGetStepUrl(?ChannelManagerConfigInterface $config, string $channelManagerName)
    {
        if (is_null($config) || !$config->isReadyToSync()) {
            $currentStepRouteName = $this
                ->getCurrentStepUrl($channelManagerName, $config);

            if ($currentStepRouteName !== $channelManagerName) {
                $routeParams = in_array($currentStepRouteName, ['wizard_info', 'cm_data_warnings'])
                    ? ['channelManagerName' => $channelManagerName]
                    : [];

                return $this->router->generate($currentStepRouteName, $routeParams);
            }
        }

        return true;
    }

    /**
     * @param string $channelManagerName
     * @return bool
     */
    public function isConfiguredByTechSupport(string $channelManagerName)
    {
        return in_array($channelManagerName, self::CHANNEL_MANAGERS_WITH_CONFIGURATION_BY_TECH_SUPPORT);
    }

    /**
     * @param string $channelManagerName
     * @param ChannelManagerConfigInterface|null $config
     * @return string
     */
    public function getCurrentStepUrl(string $channelManagerName, ?ChannelManagerConfigInterface $config)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        if (is_null($config)
            || ($this->isConfiguredByTechSupport($channelManagerName) && empty($config->getHotelId()))
            || (!$this->isConfiguredByTechSupport($channelManagerName) && !$config->isConnectionSettingsRead())) {
            return 'wizard_info';
        }

        if (!$config->isMainSettingsFilled()) {
            return $channelManagerName;
        }

        if ($config->getRooms()->isEmpty()) {
            return $channelManagerName . '_room';
        }

        if ($config->getTariffs()->isEmpty()) {
            return $channelManagerName . '_tariff';
        }

        if (!$config->isConfirmedWithDataWarnings()) {
            return 'cm_data_warnings';
        }

        throw new \RuntimeException('It is impossible to determine the current step of channel manager configuration');
    }

    /**
     * @param Hotel $hotel
     * @param string $channelManagerName
     * @return array
     */
    public function getUnfilledDataErrors(Hotel $hotel, string $channelManagerName)
    {
        $result = [];
        if (in_array($channelManagerName, ['ostrovok', 'hundred_one_hotels'])) {
            $emptyFields = $this->getUnfilledFields($hotel);

            if (!empty($emptyFields)) {
                $emptyFieldNames = array_map(
                    function ($emptyFieldName) {
                        return '"'.$this->fieldsManager->getFieldName(Hotel::class, $emptyFieldName).'"';
                    },
                    $emptyFields
                );

                $result[] = $this->translator
                    ->transChoice('cm_wizard.unfilled_data_error', count($emptyFieldNames), [
                        '%emptyFieldNames%' => join(',', $emptyFieldNames)
                    ]);
            }
        }

        return $result;
    }

    /**
     * @param Hotel $hotel
     * @return string
     */
    public function getChannelManagerHotelAddress(Hotel $hotel)
    {
        return (!empty($hotel->getCityId()) ? $this->billingApi->getCityById($hotel->getCityId())->getName() : '')
            . ($hotel->getSettlement() ? (', ' . $hotel->getSettlement()) : '')
            . (!empty($hotel->getStreet()) ? ' ул. ' . $hotel->getStreet() : '')
            . ($hotel->getHouse() ? ', ' . $hotel->getHouse() : '')
            . ($hotel->getCorpus() ? ('/' . $hotel->getCorpus()) : '');
    }

    /**
     * @param ChannelManagerConfigInterface $config
     * @param string $cacheClass
     * @return array
     */
    public function getLastCachesData(ChannelManagerConfigInterface $config, string $cacheClass)
    {
        /** @var RoomType[] $syncRoomTypes */
        $syncRoomTypes = array_unique(array_map(function(Room $room) {
            return $room->getRoomType();
        }, $config->getRooms()->toArray()), SORT_REGULAR);
        $syncRoomTypeIds = $this->helper->toIds($syncRoomTypes);

        /** @var Tariff[] $syncTariffs */
        $syncTariffs = array_unique(array_map(function(\MBH\Bundle\ChannelManagerBundle\Document\Tariff $tariff) {
            return $tariff->getTariff();
        }, $config->getTariffs()->toArray()), SORT_REGULAR);
        $syncTariffIds = $this->helper->toIds($syncTariffs);

        $lastDefinedCaches = [];
        $lastCacheByRoomTypesAndTariffs =
            $this->warningsCompiler->getLastCacheByRoomTypesAndTariffs($cacheClass, $syncRoomTypeIds, $syncTariffIds);

        foreach ($syncRoomTypes as $roomType) {
            foreach ($syncTariffs as $tariff) {
                $tariffId = $cacheClass === PriceCache::class ? $tariff->getId() : 0;
                if (isset($lastCacheByRoomTypesAndTariffs[$roomType->getId()][$tariffId])) {
                    /** @var \DateTime $date */
                    $date = $lastCacheByRoomTypesAndTariffs[$roomType->getId()][$tariffId]['date'];
                    $offsetFromNow = $date->diff(new \DateTime('midnight'))->days;
                    if ($offsetFromNow > 180) {
                        $status = 'success';
                    } elseif ($offsetFromNow > 90) {
                        $status = 'warning';
                    } else {
                        $status = 'danger';
                    }
                } else {
                    $date = null;
                    $status = 'danger';
                }

                $cacheData = [
                    'roomType' => $roomType,
                    'tariff' => $tariff,
                    'date' => $date,
                    'status' => $status
                ];

                $cacheClass === RoomCache::class
                    ? $lastDefinedCaches[] = $cacheData
                    : $lastDefinedCaches[$roomType->getId()][] = $cacheData;
            }
        }

        return $lastDefinedCaches;
    }

    /**
     * @param Hotel $hotel
     * @return array
     */
    private function getUnfilledFields(Hotel $hotel)
    {
        return $this->fieldsManager->getFieldsByCorrectnessStatuses(
            ['house', 'cityId', 'street'],
            $hotel
        )[$this->fieldsManager::EMPTY_FIELD_STATUS];
    }
}