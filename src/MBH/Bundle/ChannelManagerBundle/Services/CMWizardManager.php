<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\ChannelManagerBundle\Form\IntroType;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\HotelBundle\Document\Hotel;

class CMWizardManager
{
    private $channelManager;
    private $fieldsManager;

    public function __construct(ChannelManager $channelManager, DocumentFieldsManager $fieldsManager) {
        $this->channelManager = $channelManager;
        $this->fieldsManager = $fieldsManager;
    }

    const CHANNEL_MANAGERS_WITH_CONFIGURATION_BY_TECH_SUPPORT = [
        'hundred_one_hotels',
        'ostrovok',
        'vashotel'
    ];

    const INTRO_FORMS_BY_CM_NAMES = [
        'hundred_one_hotels' => IntroType::class,
        'ostrovok' => IntroType::class,
        'vashotel' => IntroType::class
    ];

    /**
     * @param string $channelManagerName
     * @return string
     */
    public function getIntroForm(string $channelManagerName)
    {
        $this->channelManager->checkForCMExistence($channelManagerName, true);
        //TODO: Возможно уберу, если будет требоваться только ID
        if (!array_key_exists($channelManagerName, self::INTRO_FORMS_BY_CM_NAMES)) {
            throw new \InvalidArgumentException();
        }

        return self::INTRO_FORMS_BY_CM_NAMES[$channelManagerName];
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
     * @param string $channelManagerName
     * @param ChannelManagerConfigInterface|null $config
     * @return string
     */
    public function getCurrentStepUrl(string $channelManagerName, ?ChannelManagerConfigInterface $config)
    {
        if (is_null($config) || !$config->isReadinessConfirmed()) {
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
        $emptyFields = $this->fieldsManager->getFieldsByCorrectnessStatuses([], $hotel)[$this->fieldsManager::EMPTY_FIELD_STATUS];
        if (!empty($emptyFields)) {
            $emptyFieldNames = array_map(function ($emptyFieldName) {
                return '"' . $this->fieldsManager->getFieldName(Hotel::class, $emptyFieldName) . '"';
            }, $emptyFields);

            $result[] = 'Заполните информацию об отеле в ' . (count($emptyFields) === 1 ? 'поле' : 'полях') . ': ' . join(', ', $emptyFieldNames);
        }

        return $result;
    }
}