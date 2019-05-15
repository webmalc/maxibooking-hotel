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

    public const NAME_COLOR_THEME_BLACK01 = 'black01';
    public const NAME_COLOR_THEME_BLUE02 = 'blue02';

    const COLORS_BY_THEMES = [
        'autumn'                      => [
            'main' => '#832736',
            'mainlight' => '#faebd8'
        ],
        self::NAME_COLOR_THEME_BLACK01 => [
            'main' => '#1e1e1e',
            'mainlight' => '#ebebeb'
        ],
        'black02'                     => [
            'main' => '#1e1e1e',
            'mainlight' => '#ebebeb'
        ],
        'blue'                        => [
            'main' => '#2b426b',
            'mainlight' => '#cde5fd'
        ],
        'blue01'                      => [
            'main' => '#1faaeb',
            'mainlight' => '#d4f1ff'
        ],
        self::NAME_COLOR_THEME_BLUE02 => [
            'main' => '#0099FF',
            'mainlight' => '#C3EBFF'
        ],
        'brown'                       => [
            'main' => '#53230d',
            'mainlight' => '#faf3d7'
        ],
        'fuksia'                      => [
            'main' => '#753d62',
            'mainlight' => '#fbe5e8'
        ],
        'green'                       => [
            'main' => '#37562d',
            'mainlight' => '#e1f4c6'
        ],
        'sea'                         => [
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
    private $colorTheme = self::NAME_COLOR_THEME_BLUE02;

    /**
     * @var null|string
     * @ODM\Field(type="string")
     */
    private $paymentFormId;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    private $usePaymentForm = true;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\OnlineBundle\Document\SiteContent")
     * @var array|ArrayCollection|SiteContent[]
     */
    private $content;

    public function __construct()
    {
        $this->hotels = new ArrayCollection();
        $this->socialNetworkingServices = new ArrayCollection();
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
        $this->addContent($hotel);

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

    /**
     * @return null|string
     */
    public function getPaymentFormId(): ?string
    {
        return $this->paymentFormId;
    }

    /**
     * @param null|string $paymentFormId
     */
    public function setPaymentFormId(?string $paymentFormId): void
    {
        $this->paymentFormId = $paymentFormId;
    }

    /**
     * @return bool
     */
    public function isUsePaymentForm(): bool
    {
        return $this->usePaymentForm;
    }

    /**
     * @param bool $usePaymentForm
     */
    public function setUsePaymentForm(bool $usePaymentForm): self
    {
        $this->usePaymentForm = $usePaymentForm;

        return $this;
    }

    /**
     * @return array|ArrayCollection|SiteContent[]
     */
    public function getContents(bool $forAll = false)
    {
        return $forAll
            ? $this->content
            : $this->content->filter(function (SiteContent $content) {
                return $this->hotels->contains($content->getHotel());
            });
    }

    public function getContentForHotel(Hotel $hotel): SiteContent
    {
        return $this->content->filter(function (SiteContent $content) use ($hotel) {
            return $content->getHotel() === $hotel;
        })
            ->first();
    }

    /**
     * @param array|ArrayCollection|SiteContent[] $content
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    private function addContent(Hotel $hotel): void
    {
        $this->content->add((new SiteContent())->setHotel($hotel));
    }
}
