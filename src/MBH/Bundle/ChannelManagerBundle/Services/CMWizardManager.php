<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use MBH\Bundle\ChannelManagerBundle\Form\HundredOneHotelsIntroType;
use MBH\Bundle\ChannelManagerBundle\Form\OstrovokIntroType;

class CMWizardManager
{
    private $channelManagersList;

    public function __construct(array $channelManagerSettings) {
        $this->channelManagersList = array_keys($channelManagerSettings);
    }

    const INTRO_FORMS_BY_CM_NAMES = [
        'hundred_one_hotels' => HundredOneHotelsIntroType::class,
        'ostrovok' => OstrovokIntroType::class
    ];

    /**
     * @param string $channelManagerName
     * @return string
     */
    public function getIntroForm(string $channelManagerName)
    {
        if (!array_key_exists($channelManagerName, self::INTRO_FORMS_BY_CM_NAMES)) {
            throw new \InvalidArgumentException('');
        }

        return self::INTRO_FORMS_BY_CM_NAMES[$channelManagerName];
    }

    /**
     * @param string $channelManagerName
     * @return bool
     */
    public function hasIntroForm(string $channelManagerName)
    {
        $withoutFormOnIntro = ['booking', 'expedia'];

        return !in_array($channelManagerName, $withoutFormOnIntro);
    }

    private function checkForCMExistence(string $channelManagerName)
    {
        if (!in_array($channelManagerName, $this->channelManagersList)) {
            throw new \InvalidArgumentException('Channel manager ' . $channelManagerName . '');
        }
    }
}