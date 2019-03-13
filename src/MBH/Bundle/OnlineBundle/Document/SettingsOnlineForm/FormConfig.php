<?php

namespace MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\DecorationDataInterface;
use MBH\Bundle\OnlineBundle\Document\DecorationDataTrait;
use MBH\Bundle\OnlineBundle\Document\DecorationInterface;
use MBH\Bundle\OnlineBundle\Document\DecorationTrait;
use MBH\Bundle\OnlineBundle\Document\GoogleAnalyticConfig;
use MBH\Bundle\OnlineBundle\Document\YandexAnalyticConfig;
use MBH\Bundle\OnlineBundle\Services\SiteManager;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="FormConfig", repositoryClass="MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfigRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class FormConfig extends Base implements DecorationInterface, DecorationDataInterface
{
    use DecorationTrait;
    use DecorationDataTrait;

    const paymentTypesList = [
        "in_hotel",
        "in_office",
        "by_receipt",
        "online_full",
        "online_first_day",
        "online_half",
        "by_receipt_full",
        "by_receipt_first_day",
        "by_receipt_half"
    ];

    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;

    /**
     * Hook softdeleteable behavior
     * deletedAt field
     */
    use SoftDeleteableDocument;
    
    /**
     * Hook blameable behavior
     * createdBy&updatedBy fields
     */
    use BlameableDocument;

    /**
     * @var array
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel")
     */
    protected $hotels;

    /**
     * @var RoomType[]|ArrayCollection
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\HotelBundle\Document\RoomType")
     */
    protected $roomTypeChoices;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     * @ODM\Index()
     */
    protected $enabled = true;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $roomTypes = true;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $tourists = true;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $nights = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isDisplayChildrenAges = false;

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Collection
     * @Assert\NotNull()
     * @Assert\Choice(callback = "getPaymentTypesList", multiple = true)
     */
    protected $paymentTypes = [];

    /**
     * @var string
     * @Gedmo\Versioned()
     * @ODM\Field(type="string")
     */
    protected $js;

    /**
     * @var string
     * @Gedmo\Versioned
     * @Assert\NotNull()
     * @Assert\Url()
     * @ODM\Field(type="string")
     */
    protected $resultsUrl;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=1, max = 20
     * )
     */
    private $maxPackages = 5;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    private $personalDataPolicies;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     * @Gedmo\Versioned
     * @Assert\NotNull()
     */
    private $requestInn = false;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     * @Gedmo\Versioned
     * @Assert\NotNull()
     */
    private $requestTouristDocumentNumber = false;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     * @Gedmo\Versioned
     * @Assert\NotNull()
     */
    private $requestPatronymic = false;

    /**
     * @var GoogleAnalyticConfig
     * @ODM\EmbedOne(targetDocument="MBH\Bundle\OnlineBundle\Document\GoogleAnalyticConfig")
     */
    private $googleAnalyticConfig;

    /**
     * @var YandexAnalyticConfig
     * @ODM\EmbedOne(targetDocument="MBH\Bundle\OnlineBundle\Document\YandexAnalyticConfig")
     */
    private $yandexAnalyticConfig;

    /**
     * @return GoogleAnalyticConfig
     */
    public function getGoogleAnalyticConfig(): ?GoogleAnalyticConfig
    {
        return $this->googleAnalyticConfig ?? new GoogleAnalyticConfig();
    }

    /**
     * @param GoogleAnalyticConfig $googleAnalyticConfig
     * @return FormConfig
     */
    public function setGoogleAnalyticConfig(GoogleAnalyticConfig $googleAnalyticConfig): FormConfig
    {
        $this->googleAnalyticConfig = $googleAnalyticConfig;

        return $this;
    }

    /**
     * @return YandexAnalyticConfig
     */
    public function getYandexAnalyticConfig(): ?YandexAnalyticConfig
    {
        return $this->yandexAnalyticConfig ?? new YandexAnalyticConfig();
    }

    /**
     * @param YandexAnalyticConfig $yandexAnalyticConfig
     * @return FormConfig
     */
    public function setYandexAnalyticConfig(YandexAnalyticConfig $yandexAnalyticConfig): FormConfig
    {
        $this->yandexAnalyticConfig = $yandexAnalyticConfig;

        return $this;
    }

    /**
     * @ODM\Field(type="boolean")
     * @var bool
     */
    private $forMbSite = false;

    /**
     * @return bool
     */
    public function isRequestPatronymic(): ?bool
    {
        return $this->requestPatronymic;
    }

    /**
     * @param bool $requestPatronymic
     * @return FormConfig
     */
    public function setRequestPatronymic(bool $requestPatronymic): FormConfig
    {
        $this->requestPatronymic = $requestPatronymic;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequestInn(): ?bool
    {
        return $this->requestInn;
    }

    /**
     * @param bool $requestInn
     * @return FormConfig
     */
    public function setRequestInn(bool $requestInn): FormConfig
    {
        $this->requestInn = $requestInn;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequestTouristDocumentNumber(): ?bool
    {
        return $this->requestTouristDocumentNumber;
    }

    /**
     * @param bool $requestTouristDocumentNumber
     * @return FormConfig
     */
    public function setRequestTouristDocumentNumber(bool $requestTouristDocumentNumber): FormConfig
    {
        $this->requestTouristDocumentNumber = $requestTouristDocumentNumber;

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
     * @param string $personalDataPolicies
     * @return FormConfig
     */
    public function setPersonalDataPolicies(string $personalDataPolicies)
    {
        $this->personalDataPolicies = $personalDataPolicies;

        return $this;
    }

    /**
     * @return string
     */
    public function getJs(): ?string
    {
        return $this->js;
    }

    /**
     * @param string $js
     * @return FormConfig
     */
    public function setJs(string $js = null): FormConfig
    {
        $this->js = $js;

        return $this;
    }

    public function __construct()
    {
        $this->roomTypeChoices = new ArrayCollection();
        $this->hotels = new ArrayCollection();
        $this->googleAnalyticConfig = new GoogleAnalyticConfig();
        $this->yandexAnalyticConfig = new YandexAnalyticConfig();
    }

    /**
     * maxPackages set
     *
     * @param int $maxPackages
     * @return self
     */
    public function setMaxPackages(int $maxPackages): self
    {
        $this->maxPackages = $maxPackages;

        return $this;
    }

    /**
     * maxPackages get
     *
     * @return int
     */
    public function getMaxPackages(): int
    {
        return $this->maxPackages;
    }

    /**
     * @return bool
     */
    public function isIsDisplayChildrenAges(): ?bool
    {
        return $this->isDisplayChildrenAges;
    }

    /**
     * @param bool $isDisplayChildrenAges
     * @return FormConfig
     */
    public function setIsDisplayChildrenAges(bool $isDisplayChildrenAges): FormConfig
    {
        $this->isDisplayChildrenAges = $isDisplayChildrenAges;

        return $this;
    }

    /**
     * @return array
     */
    public static function getPaymentTypesList()
    {
        return self::paymentTypesList;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set roomTypes
     *
     * @param boolean $roomTypes
     * @return self
     */
    public function setRoomTypes($roomTypes)
    {
        $this->roomTypes = $roomTypes;
        return $this;
    }

    /**
     * Get roomTypes
     *
     * @return boolean $roomTypes
     */
    public function getRoomTypes()
    {
        return $this->roomTypes;
    }

    /**
     * Set tourists
     *
     * @param boolean $tourists
     * @return self
     */
    public function setTourists($tourists)
    {
        $this->tourists = $tourists;
        return $this;
    }

    /**
     * Get tourists
     *
     * @return boolean $tourists
     */
    public function getTourists()
    {
        return $this->tourists;
    }

    /**
     * Set paymentTypes
     *
     * @param array $paymentTypes
     * @return self
     */
    public function setPaymentTypes($paymentTypes)
    {
        $this->paymentTypes = $paymentTypes;
        return $this;
    }

    /**
     * Get paymentTypes
     *
     * @param boolean $online
     * @return array $paymentTypes
     */
    public function getPaymentTypes($online = true)
    {
        if (!$online) {
            $this->paymentTypes = array_diff($this->paymentTypes, ["online_full", "online_first_day", "online_half"]);
        }

        return $this->paymentTypes;
    }

    /**
     * Set nights
     *
     * @param boolean $nights
     * @return self
     */
    public function setNights($nights)
    {
        $this->nights = $nights;

        return $this;
    }

    /**
     * Get nights
     *
     * @return boolean $nights
     */
    public function getNights()
    {
        return $this->nights;
    }

    /**
     * @return array|ArrayCollection|Hotel[]
     */
    public function getHotels()
    {
        return $this->hotels;
    }

    /**
     * @param array|ArrayCollection $hotels
     * @return FormConfig
     */
    public function setHotels(array $hotels)
    {
        $this->hotels = new ArrayCollection($hotels);

        return $this;
    }
    
    /**
     * @return ArrayCollection|RoomType[]
     */
    public function getRoomTypeChoices()
    {
        return $this->roomTypeChoices;
    }

    /**
     * @param array $roomTypeChoices
     * @return FormConfig
     */
    public function setRoomTypeChoices($roomTypeChoices)
    {
        $this->roomTypeChoices = $roomTypeChoices;

        return $this;
    }

    /**
     * @param RoomType $roomType
     * @return FormConfig
     */
    public function removeRoomType(RoomType $roomType)
    {
        $this->roomTypeChoices->removeElement($roomType);

        return $this;
    }

    /**
     * If form config is used for api, results url contains only domain address
     * @param bool $forResultsPage
     * @return string
     */
    public function getResultsUrl($forResultsPage = false): ?string
    {
        return $forResultsPage && $this->isForMbSite()
            ? $this->resultsUrl . SiteManager::DEFAULT_RESULTS_PAGE
            : $this->resultsUrl;
    }

    /**
     * @param string $resultsUrl
     * @return FormConfig
     */
    public function setResultsUrl(string $resultsUrl = null)
    {
        $this->resultsUrl = $resultsUrl;
        return $this;
    }

    /**
     * @return bool
     */
    public function isForMbSite(): ?bool
    {
        return $this->forMbSite;
    }

    /**
     * @param bool $forMbSite
     * @return FormConfig
     */
    public function setForMbSite(bool $forMbSite): FormConfig
    {
        $this->forMbSite = $forMbSite;

        return $this;
    }
}
