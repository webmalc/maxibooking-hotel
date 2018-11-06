<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document
 * Class SiteConfig
 * @package MBH\Bundle\OnlineBundle\Document
 */
class SiteConfig extends Base
{
    public const SCHEME = 'https';
    public const DOMAIN = '.maaaxi.com';
    public const FAKE_DOMAIN_FOR_DEV = 'fakeDomain';

    const COLORS_BY_THEMES = [
        'autumn' => [
            'main' => '#832736',
            'mainlight' => '#faebd8'
        ],
        'black01' => [
            'main' => '#1e1e1e',
            'mainlight' => '#ebebeb'
        ],
        'black02' => [
            'main' => '#1e1e1e',
            'mainlight' => '#ebebeb'
        ],
        'blue' => [
            'main' => '#2b426b',
            'mainlight' => '#cde5fd'
        ],
        'blue01' => [
            'main' => '#1faaeb',
            'mainlight' => '#d4f1ff'
        ],
        'blue02' => [
            'main' => '#1faaeb',
            'mainlight' => '#d4f1ff'
        ],
        'brown' => [
            'main' => '#53230d',
            'mainlight' => '#faf3d7'
        ],
        'fuksia' => [
            'main' => '#753d62',
            'mainlight' => '#fbe5e8'
        ],
        'green' => [
            'main' => '#37562d',
            'mainlight' => '#e1f4c6'
        ],
        'sea' => [
            'main' => '#018788',
            'mainlight' => '#d8f5fb'
        ]
    ];

    /**
     * @ODM\Field(type="collection")
     * @var array
     */
    private $keyWords = [];

    /**
     * @ODM\Field(type="string")
     * @var string
     */
    private $personalDataPolicies;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $contract;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     * @var array|ArrayCollection|Hotel[]
     */
    private $hotels;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotBlank()
     */
    private $siteDomain;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotBlank()
     */
    private $scheme = self::SCHEME;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotBlank()
     */
    private $domain = self::DOMAIN;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $colorTheme = 'black01';

    public function __construct()
    {
        $this->hotels = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getColorTheme(): ?string
    {
        return $this->colorTheme;
    }

    /**
     * @param string $colorTheme
     * @return SiteConfig
     */
    public function setColorTheme(?string $colorTheme): SiteConfig
    {
        $this->colorTheme = $colorTheme;

        return $this;
    }

    /**
     * @return array
     */
    public function getKeyWords(): ?array
    {
        return $this->keyWords;
    }

    /**
     * @param array $keyWords
     * @return SiteConfig
     */
    public function setKeyWords(array $keyWords): SiteConfig
    {
        $this->keyWords = $keyWords;

        return $this;
    }

    /**
     * @return string
     */
    public function getPersonalDataPolicies(): ?string
    {
        return $this->personalDataPolicies;
    }

    /**
     * @param null|string $personalDataPolicies
     * @return SiteConfig
     */
    public function setPersonalDataPolicies(?string $personalDataPolicies): SiteConfig
    {
        $this->personalDataPolicies = $personalDataPolicies;

        return $this;
    }

    /**
     * @return string
     */
    public function getContract(): ?string
    {
        return $this->contract;
    }

    /**
     * @param string $contract
     * @return SiteConfig
     */
    public function setContract(?string $contract): SiteConfig
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * @return array|ArrayCollection|Hotel[]
     */
    public function getHotels()
    {
        return $this->hotels;
    }

    /**
     * @param array|ArrayCollection|Hotel[] $hotels
     * @return SiteConfig
     */
    public function setHotels($hotels)
    {
        $this->hotels = $hotels;

        return $this;
    }

    /**
     * @param Hotel $hotel
     * @return SiteConfig
     */
    public function addHotel(Hotel $hotel)
    {
        $this->hotels->add($hotel);

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteDomain(): ?string
    {
        return $this->siteDomain;
    }

    /**
     * @param string $siteDomain
     * @return SiteConfig
     */
    public function setSiteDomain(?string $siteDomain): SiteConfig
    {
        $this->siteDomain = $siteDomain;

        return $this;
    }

    /**
     * @return array
     */
    public function getThemeColors()
    {
        return self::COLORS_BY_THEMES[$this->getColorTheme()];
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @param string $scheme
     */
    public function setScheme(string $scheme): void
    {
        $this->scheme = $scheme;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }
}