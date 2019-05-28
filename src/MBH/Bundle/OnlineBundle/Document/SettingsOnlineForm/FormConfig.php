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
 * @ODM\Document(collection="FormConfig")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class FormConfig extends Base implements DecorationInterface, DecorationDataInterface
{
    use DecorationTrait;
    use DecorationDataTrait;

    public const ROUTER_NAME_SEARCH_IFRAME = 'search_form_main_iframe';
    public const ROUTER_NAME_CALENDAR_IFRAME = 'search_form_calendar_iframe';
    public const ROUTER_NAME_ADDITIONAL_IFRAME = 'search_form_additional_form_iframe';
    public const ROUTER_NAME_LOAD_ALL_IFRAME = 'search_form_load_all_iframe_for_search';

    public const PAYMENT_TYPE_IN_HOTEL = 'in_hotel';
    public const PAYMENT_TYPE_IN_OFFICE = 'in_office';
    public const PAYMENT_TYPE_ONLINE_FULL = 'online_full';
    public const PAYMENT_TYPE_ONLINE_FIRST_DAY = 'online_first_day';
    public const PAYMENT_TYPE_ONLINE_HALF = 'online_half';
    public const PAYMENT_TYPE_BY_RECEIPT = 'by_receipt';
    public const PAYMENT_TYPE_BY_RECEIPT_FULL = 'by_receipt_full';
    public const PAYMENT_TYPE_BY_RECEIPT_FIRST_DAY = 'by_receipt_first_day';
    public const PAYMENT_TYPE_BY_RECEIPT_HALF = 'by_receipt_half';

    public const PAYMENT_TYPES_LIST = [
        self::PAYMENT_TYPE_IN_HOTEL,
        self::PAYMENT_TYPE_IN_OFFICE,
        self::PAYMENT_TYPE_BY_RECEIPT,
        self::PAYMENT_TYPE_ONLINE_FULL,
        self::PAYMENT_TYPE_ONLINE_FIRST_DAY,
        self::PAYMENT_TYPE_ONLINE_HALF,
        self::PAYMENT_TYPE_BY_RECEIPT_FULL,
        self::PAYMENT_TYPE_BY_RECEIPT_FIRST_DAY,
        self::PAYMENT_TYPE_BY_RECEIPT_HALF
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
     * @ODM\Field(type="collection")
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
     * @ODM\Field(type="integer")
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
     * @ODM\Field(type="boolean")
     * @Gedmo\Versioned
     * @Assert\NotNull()
     */
    private $requestInn = false;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     * @Gedmo\Versioned
     * @Assert\NotNull()
     */
    private $requestTouristDocumentNumber = false;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
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
     * @ODM\Field(type="boolean")
     * @var bool
     */
    private $forMbSite = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    private $useAdditionalForm = false;

    /**
     * @var FieldsName
     * @ODM\EmbedOne(targetDocument="MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FieldsName")
     */
    private $fieldsName;

    /**
     * @var bool
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    private $iconLogoLink = false;

    /**
     * @var string | null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    private $resultFormCss;

    /**
     * @var string | null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    private $additionalFormCss;

    /**
     * @var string | null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    private $additionalFormJs;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    private $additionalFormFrameWidth = '270px';

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    private $additionalFormFrameHeight = 'auto';

    /**
     * @var string | null
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    private $calendarCss;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    private $calendarFrameWidth = '310px';

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    private $calendarFrameHeight = '270px';

    public function __construct()
    {
        $this->roomTypeChoices = new ArrayCollection();
        $this->hotels = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getResultFormCss(): ?string
    {
        return $this->resultFormCss;
    }

    /**
     * @param string|null $resultFormCss
     */
    public function setResultFormCss(?string $resultFormCss): self
    {
        $this->resultFormCss = $resultFormCss;

        return $this;
    }

    /**
     * @return FieldsName
     */
    public function getFieldsName(): FieldsName
    {
        return $this->fieldsName ?? new FieldsName();
    }

    /**
     * @param FieldsName $fieldsName
     */
    public function setFieldsName(FieldsName $fieldsName): self
    {
        $this->fieldsName = $fieldsName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUseAdditionalForm(): bool
    {
        return $this->useAdditionalForm;
    }

    /**
     * @param bool $useAdditionalForm
     */
    public function setUseAdditionalForm(bool $useAdditionalForm): self
    {
        $this->useAdditionalForm = $useAdditionalForm;

        return $this;
    }

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
    public function isDisplayChildrenAges(): ?bool
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
        return self::PAYMENT_TYPES_LIST;
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
    public function isEnabled()
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
            $this->paymentTypes = array_diff(
                $this->paymentTypes,
                [
                    self::PAYMENT_TYPE_ONLINE_FULL,
                    self::PAYMENT_TYPE_ONLINE_FIRST_DAY,
                    self::PAYMENT_TYPE_ONLINE_HALF
                ]
            );
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

    /**
     * @return bool
     */
    public function isIconLogoLink(): bool
    {
        return $this->iconLogoLink;
    }

    /**
     * @param bool $iconLogoLink
     */
    public function setIconLogoLink(bool $iconLogoLink): self
    {
        $this->iconLogoLink = $iconLogoLink;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdditionalFormCss(): ?string
    {
        return $this->additionalFormCss;
    }

    /**
     * @param string|null $additionalFormCss
     */
    public function setAdditionalFormCss(?string $additionalFormCss): self
    {
        $this->additionalFormCss = $additionalFormCss;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdditionalFormJs(): ?string
    {
        return $this->additionalFormJs;
    }

    /**
     * @param string|null $additionalFormJs
     */
    public function setAdditionalFormJs(?string $additionalFormJs): self
    {
        $this->additionalFormJs = $additionalFormJs;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdditionalFormFrameWidth(): string
    {
        return $this->additionalFormFrameWidth;
    }

    /**
     * @param string $additionalFormFrameWidth
     */
    public function setAdditionalFormFrameWidth(string $additionalFormFrameWidth): self
    {
        $this->additionalFormFrameWidth = $additionalFormFrameWidth;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdditionalFormFrameHeight(): string
    {
        return $this->additionalFormFrameHeight;
    }

    /**
     * @param string $additionalFormFrameHeight
     */
    public function setAdditionalFormFrameHeight(string $additionalFormFrameHeight): self
    {
        $this->additionalFormFrameHeight = $additionalFormFrameHeight;

        return $this;
    }

    /**
     * @return string | null
     */
    public function getCalendarCss(): ?string
    {
        return $this->calendarCss;
    }

    /**
     * @param string $calendarCss
     */
    public function setCalendarCss(?string $calendarCss): self
    {
        $this->calendarCss = $calendarCss;

        return $this;
    }

    /**
     * @return string
     */
    public function getCalendarFrameWidth(): string
    {
        return $this->calendarFrameWidth;
    }

    /**
     * @param string $calendarFrameWidth
     */
    public function setCalendarFrameWidth(string $calendarFrameWidth): self
    {
        $this->calendarFrameWidth = $calendarFrameWidth;

        return $this;
    }

    /**
     * @return string
     */
    public function getCalendarFrameHeight(): string
    {
        return $this->calendarFrameHeight;
    }

    /**
     * @param string $calendarFrameHeight
     */
    public function setCalendarFrameHeight(string $calendarFrameHeight): self
    {
        $this->calendarFrameHeight = $calendarFrameHeight;

        return $this;
    }

}
