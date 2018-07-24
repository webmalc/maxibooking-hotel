<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\ChannelManagerBundle\Form\IntroType;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class CMWizardManager
{
    private $channelManager;
    private $fieldsManager;
    private $tokenStorage;
    private $billingApi;
    private $translator;

    public function __construct(
        ChannelManager $channelManager,
        DocumentFieldsManager $fieldsManager,
        TokenStorage $tokenStorage,
        BillingApi $billingApi,
        TranslatorInterface $translator
    ) {
        $this->channelManager = $channelManager;
        $this->fieldsManager = $fieldsManager;
        $this->tokenStorage = $tokenStorage;
        $this->billingApi = $billingApi;
        $this->translator = $translator;
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
        $this->channelManager->checkForCMExistence($channelManagerName, true);
        if (!$this->isConfiguredByTechSupport($channelManagerName)) {
            throw new \InvalidArgumentException($channelManagerName . ' is configured by tech support!');
        }

        return IntroType::class;
    }

    /**
     * @param string $channelManagerName
     * @return bool
     */
    public function isConfiguredByTechSupport(string $channelManagerName)
    {
        $this->channelManager->checkForCMExistence($channelManagerName, true);

        return in_array($channelManagerName, self::CHANNEL_MANAGERS_WITH_CONFIGURATION_BY_TECH_SUPPORT);
    }

    /**
     * @param Hotel $hotel
     * @param string $channelManagerName
     * @param string $channelManagerHumanName
     * @return array
     */
    public function getConnectionInfoMessages(Hotel $hotel, string $channelManagerName, string $channelManagerHumanName)
    {
        $result = [];
        if ($this->isConfiguredByTechSupport($channelManagerName)) {
            $result[] = $this->translator->trans('cm_wizard_manager.hotel_name_notification.text', [
                '%channelManagerName%' => $channelManagerHumanName,
                '%hotelName%' => $hotel->getName()
            ]);
        }
        
        if (in_array($channelManagerName, ['ostrovok', 'hundred_one_hotels']) && empty($this->getUnfilledFields($hotel))) {
            $result[] = $this->translator->trans('cm_wizard_manager.hotel_address_notification.text', [
                '%channelManagerName%' => $channelManagerHumanName,
                '%hotelAddress%' => $this->getChannelManagerHotelAddress($hotel)
            ]);
        }
        
        return $result;
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
            || (!$this->isConfiguredByTechSupport($channelManagerName) && !$config->isReadinessConfirmed())) {
            return 'wizard_info';
        }

        if (is_null($config) or !$config->isMainSettingsFilled() or !$config->isReadinessConfirmed()) {
            return $channelManagerName;
        }

        if ($config->getRooms()->isEmpty()) {
            return $channelManagerName . '_room';
        }

        if ($config->getTariffs()->isEmpty()) {
            return $channelManagerName . '_tariff';
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

                $result[] = 'Заполните информацию об отеле в '
                    .(count($emptyFields) === 1 ? 'поле' : 'полях')
                    .': '.join(', ', $emptyFieldNames);
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
        return $this->billingApi->getCityById($hotel->getCityId())->getName()
            . ($hotel->getSettlement() ? (', ' . $hotel->getSettlement()) : '')
            . ' ул. ' . $hotel->getStreet()
            . ', ' . $hotel->getHouse()
            . ($hotel->getCorpus() ? ('/' . $hotel->getCorpus()) : '');
    }

    private function getUnfilledFields(Hotel $hotel)
    {
        return $this->fieldsManager->getFieldsByCorrectnessStatuses(
            ['house', 'cityId', 'street'],
            $hotel
        )[$this->fieldsManager::EMPTY_FIELD_STATUS];
    }
}