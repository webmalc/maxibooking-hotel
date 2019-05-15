<?php
/**
 * Created by PhpStorm.
 * Date: 22.11.18
 */

namespace MBH\Bundle\OnlineBundle\Lib\SocialNetworking;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\SocialLink\AggregatorService;
use MBH\Bundle\OnlineBundle\Document\SocialLink\SocialLink;
use MBH\Bundle\OnlineBundle\Document\SocialLink\SocialService;

class HolderSocialLinks
{
    /**
     * @var ArrayCollection|SocialService[]
     */
    private $socialServices;

    /**
     * @var ArrayCollection|AggregatorService[]
     */
    private $aggregatorServices;

    /**
     * @var Hotel
     */
    private $hotel;

    public function __construct(Hotel $hotel)
    {
        $this->socialServices = new ArrayCollection();
        $this->aggregatorServices = new ArrayCollection();

        $this->hotel = $hotel;
    }

    /**
     * @return Hotel
     */
    public function getHotel(): Hotel
    {
        return $this->hotel;
    }

    /**
     * @return ArrayCollection|SocialService[]
     */
    public function getSocialServices(): ArrayCollection
    {
        return $this->socialServices;
    }

    /**
     * @return ArrayCollection|AggregatorService[]
     */
    public function getAggregatorServices(): ArrayCollection
    {
        return $this->aggregatorServices;
    }

    public function deleteEmptyUrl(): void
    {
        $this->socialServices = $this->getSocialServices()->filter($this->filterForClearUrl());

        $this->aggregatorServices = $this->getAggregatorServices()->filter($this->filterForClearUrl());
    }

    private function filterForClearUrl(): \Closure
    {
        return function ($socialLink) {
            /** @var SocialLink $socialLink */
            return $socialLink->getUrl() !== null;
        };
    }
}
