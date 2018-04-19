<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="FormConfig", repositoryClass="MBH\Bundle\OnlineBundle\Document\FormConfigRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class FormConfig extends Base
{
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

    const THEMES = [
        "cerulean" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cerulean/bootstrap.min.css",
        "cosmo" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cosmo/bootstrap.min.css",
        "cyborg" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cyborg/bootstrap.min.css",
        "darkly" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/darkly/bootstrap.min.css",
        "flatly" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/flatly/bootstrap.min.css",
        "journal" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/journal/bootstrap.min.css",
        "lumen" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/lumen/bootstrap.min.css",
        "paper" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/paper/bootstrap.min.css",
        "readable" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/readable/bootstrap.min.css",
        "sandstone" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/sandstone/bootstrap.min.css",
        "simplex" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/simplex/bootstrap.min.css",
        "slate" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/slate/bootstrap.min.css",
        "spacelab" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/spacelab/bootstrap.min.css",
        "superhero" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/superhero/bootstrap.min.css",
        "united" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/united/bootstrap.min.css",
        "yeti" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/yeti/bootstrap.min.css",
        "bootstrap" => "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css",
    ];

    const CSS_LIBRARIES = [
        "font_awesome" => "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"
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
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $css;

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
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Choice(callback = "getThemes")
     */
    protected $theme;
    
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
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     */
    private $frameWidth = 300;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     * @Assert\Type(type="numeric")
     */
    private $frameHeight = 400;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     * @Assert\Type(type="bool")
     */
    private $isFullWidth = false;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\Type(type="string")
     * @Assert\Length(
     *     max=65536
     * )
     */
    private $formTemplate;

    /**
     * @var array
     * @Gedmo\Versioned
     * @ODM\Collection
          * @Assert\Choice(callback = "getCssLibrariesList", multiple = true)
     */
    private $cssLibraries;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     */
    private $isHorizontal = false;

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
     * @return bool
     */
    public function isHorizontal(): ?bool
    {
        return $this->isHorizontal;
    }

    /**
     * @param bool $isHorizontal
     * @return FormConfig
     */
    public function setIsHorizontal(bool $isHorizontal): FormConfig
    {
        $this->isHorizontal = $isHorizontal;

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
     * @return bool
     */
    public function isFullWidth(): bool
    {
        return $this->isFullWidth;
    }

    /**
     * @param bool $isFullWidth
     * @return FormConfig
     */
    public function setIsFullWidth(bool $isFullWidth): FormConfig
    {
        $this->isFullWidth = $isFullWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrameWidth(): int
    {
        return $this->frameWidth;
    }

    /**
     * @param int $frameWidth
     * @return FormConfig
     */
    public function setFrameWidth(int $frameWidth): FormConfig
    {
        $this->frameWidth = $frameWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrameHeight(): int
    {
        return $this->frameHeight;
    }

    /**
     * @param int $frameHeight
     * @return FormConfig
     */
    public function setFrameHeight(int $frameHeight): FormConfig
    {
        $this->frameHeight = $frameHeight;

        return $this;
    }

    public function __construct()
    {
        $this->roomTypeChoices = new ArrayCollection();
        $this->hotels = new ArrayCollection();
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
     * @return array
     */
    public static function getThemes()
    {
        return self::THEMES;
    }

    public static function getCssLibrariesList()
    {
        return self::CSS_LIBRARIES;
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
     * @param array $hotels
     * @return FormConfig
     */
    public function setHotels($hotels)
    {
        $this->hotels = $hotels;
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
     * @return string
     */
    public function getCss(): ?string
    {
        return $this->css;
    }

    /**
     * @param string $css
     * @return FormConfig
     */
    public function setCss(string $css = null)
    {
        $this->css = $css;
        return $this;
    }

    /**
     * @return string
     */
    public function getTheme(): ?string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     * @return FormConfig
     */
    public function setTheme(string $theme = null)
    {
        $this->theme = $theme;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getResultsUrl(): ?string
    {
        return $this->resultsUrl;
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
     * @return string
     */
    public function getFormTemplate(): ?string
    {
        return $this->formTemplate;
    }

    /**
     * @param string $formTemplate
     * @return FormConfig
     */
    public function setFormTemplate(string $formTemplate = null): FormConfig
    {
        $this->formTemplate = $formTemplate;

        return $this;
    }

    /**
     * @return array
     */
    public function getCssLibraries()
    {
        return $this->cssLibraries;
    }

    /**
     * @param array $cssLibraries
     * @return FormConfig
     */
    public function setCssLibraries(array $cssLibraries = null): FormConfig
    {
        $this->cssLibraries = $cssLibraries;

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
