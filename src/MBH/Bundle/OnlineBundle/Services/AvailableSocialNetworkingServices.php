<?php
/**
 * Created by PhpStorm.
 * Date: 22.11.18
 */

namespace MBH\Bundle\OnlineBundle\Services;


use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Lib\SocialNetworking\HolderSNSs;
use MBH\Bundle\OnlineBundle\Document\SocialNetworkingService;

class AvailableSocialNetworkingServices
{
    private const NAMES = [
        'facebook'    => 'Facebook',
        'instagram'   => 'Instagram',
        'linkedin'    => 'LinkedIn',
        'twitter'     => 'Twitter',
        'youtube'     => 'Youtube',
        'google-plus' => 'Google+',
        'vk'          => 'VK',
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

    public function get(): array
    {
        $temp = self::NAMES;

        if ($this->locale === 'en') {
            unset($temp['vk']);
        }

        return $temp;
    }

    public function createHolder(SiteConfig $siteConfig): HolderSNSs
    {
        $holder = new HolderSNSs();

        /** @var SocialNetworkingService[] $usedSNSs */
        $usedSNSs = $siteConfig->getSocialNetworkingServices()->toArray();

        foreach ($this->get() as $key => $name) {
            if (isset($usedSNSs[$key])) {
                $holder->getSnss()->set($key, $usedSNSs[$key]);
            } else {
                $holder->getSnss()->set($key, new SocialNetworkingService($key, $name, null));
            }
        }

        return $holder;
    }
}