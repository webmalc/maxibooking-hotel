<?php
/**
 * Date: 15.05.19
 */

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\PersistentCollection;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\SocialLink\AggregatorService;
use MBH\Bundle\OnlineBundle\Document\SocialLink\SocialService;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SiteContent
 * @package MBH\Bundle\OnlineBundle\Document
 *
 * @ODM\Document
 */
class SiteContent extends Base
{
    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    private $useBanner = true;

    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     * @var Hotel
     */
    private $hotel;

    /**
     * @ODM\EmbedMany(targetDocument="MBH\Bundle\OnlineBundle\Document\SocialLink\SocialService", strategy="set")
     * strategy="set" для хранения как ассоциативный массив
     * @var PersistentCollection|SocialService[]
     */
    private $socialNetworkingServices;

    /**
     * @ODM\EmbedMany(targetDocument="MBH\Bundle\OnlineBundle\Document\SocialLink\AggregatorService", strategy="set")
     * strategy="set" для хранения как ассоциативный массив
     * @var PersistentCollection|AggregatorService[]
     */
    private $aggregatorServices;


    /**
     * @return bool
     */
    public function isUseBanner(): bool
    {
        return $this->useBanner;
    }

    /**
     * @param bool $useBanner
     */
    public function setUnUseBanner(): self
    {
        $this->useBanner = false;

        return $this;
    }

    /**
     * @param bool $useBanner
     */
    public function setUseBanner(bool $useBanner): self
    {
        $this->useBanner = $useBanner;

        return $this;
    }

    /**
     * @return Hotel
     */
    public function getHotel(): Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     */
    public function setHotel(Hotel $hotel): self
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * @return PersistentCollection|SocialService[]
     */
    public function getSocialNetworkingServices()
    {
        return $this->socialNetworkingServices;
    }

    /**
     * @param SocialService[] $socialNetworkingServices
     */
    public function setSocialNetworkingServices($socialNetworkingServices): self
    {
        $this->socialNetworkingServices = $socialNetworkingServices;

        return $this;
    }

    /**
     * @return PersistentCollection|AggregatorService[]
     */
    public function getAggregatorServices()
    {
        return $this->aggregatorServices;
    }

    /**
     * @param AggregatorService[] $aggregatorServices
     */
    public function setAggregatorServices($aggregatorServices): self
    {
        $this->aggregatorServices = $aggregatorServices;

        return $this;
    }
}
