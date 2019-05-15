<?php
/**
 * Created by PhpStorm.
 * Date: 22.11.18
 */

namespace MBH\Bundle\OnlineBundle\Services;


use MBH\Bundle\OnlineBundle\Document\SiteContent;
use MBH\Bundle\OnlineBundle\Document\SocialLink\AggregatorService;
use MBH\Bundle\OnlineBundle\Lib\SocialNetworking\HolderSocialLinks;
use MBH\Bundle\OnlineBundle\Document\SocialLink\SocialService;

class AvailableSocialNetworkingServices
{
    private const SOCIAL_NAMES = [
        'facebook'      => 'Facebook',
        'instagram'     => 'Instagram',
        'linkedin'      => 'LinkedIn',
        'twitter'       => 'Twitter',
        'youtube'       => 'Youtube',
        'google-plus'   => 'Google+',
        'vk'            => 'VK',
        'odnoklassniki' => 'Одноклассники',
    ];

    private const AGGREGATOR_NAMES = [
        'airbnb'      => 'Airbnb',
        'booking'     => 'Booking.com',
        'facebook'    => 'Facebook',
        'tripadvisor' => 'TripAdvisor',
    ];

    /**
     * @var string
     */
    private $locale;

    /**
     * AvailableSocialNetworkingServices constructor.
     * @param string $locale
     */
    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public function createHolder(SiteContent $content): HolderSocialLinks
    {
        $holder = new HolderSocialLinks($content->getHotel());

        /** @var SocialService[] $usedSNSs */
        $usedSNSs = $content->getSocialNetworkingServices()->toArray();

        foreach ($this->getSocialServices() as $key => $name) {
            if (isset($usedSNSs[$key])) {
                $holder->getSocialServices()->set($key, $usedSNSs[$key]);
            } else {
                $holder->getSocialServices()->set($key, new SocialService($key, $name, null));
            }
        }

        $usedAggregatorServices = $content->getAggregatorServices()->toArray();

        foreach ($this->getAggregatorServices() as $key => $name) {
            if (isset($usedAggregatorServices[$key])) {
                $holder->getAggregatorServices()->set($key, $usedAggregatorServices[$key]);
            } else {
                $holder->getAggregatorServices()->set($key, new AggregatorService($key, $name, null));
            }
        }

        return $holder;
    }

    private function getAggregatorServices(): array
    {
        return self::AGGREGATOR_NAMES;
    }

    private function getSocialServices(): array
    {
        $temp = self::SOCIAL_NAMES;

        if ($this->locale === 'en') {
            unset($temp['vk']);
            unset($temp['odnoklassniki']);
        }

        return $temp;
    }
}
