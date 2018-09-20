<?php

namespace MBH\Bundle\PriceBundle\Document;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Services\ApiHandler;
use MBH\Bundle\PriceBundle\Document\Traits\ConditionsTrait;
use MBH\Bundle\PriceBundle\Lib\ConditionsInterface;
use MBH\Bundle\PriceBundle\Validator\Constraints as MBHValidator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Tariffs", repositoryClass="MBH\Bundle\PriceBundle\Document\TariffRepository")
 * @Gedmo\Loggable
 * @MBHValidator\Tariff
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @MongoDBUnique(fields={"fullTitle", "hotel"}, message="mbhpricebundle.document.tariff.takoy.tarif.uzhe.sushchestvuyet")
 * @ODM\HasLifecycleCallbacks
 */
class Tariff extends Base implements ConditionsInterface
{
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

    use ConditionsTrait;
    
    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Hotel", inversedBy="tariffs")
     * @Assert\NotNull(message="document.tariff.hotel.hotel_not_chosen")
     * @ODM\Index()
     */
    protected $hotel;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullTitle")
     * @Assert\NotNull()
     * @Assert\Length(
     *      min=2,
     *      minMessage="document.tariff.full_title.too_short",
     *      max=100,
     *      maxMessage="document.tariff.full_title.too_long"
     * )
     * @ODM\Index()
     */
    protected $fullTitle;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="title")
     * @Assert\Length(
     *      min=2,
     *      minMessage="document.tariff.full_title.too_short",
     *      max=100,
     *      maxMessage="document.tariff.title.too_long"
     * )
     * @ODM\Index()
     */
    protected $title;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="document.tariff.description.too_short",
     *      max=300,
     *      maxMessage="document.tariff.description.too_long"
     * )
     * @ODM\Index()
     */
    protected $description;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isDefault")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     * @ODM\Index()
     */
    protected $isDefault = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isOnline")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     * @ODM\Index()
     */
    protected $isOnline = true;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\Date()
     * @ODM\Index()
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\Date()
     * @ODM\Index()
     */
    protected $end;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0, max=18)
     */
    protected $childAge = 7;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0, max=18)
     */
    protected $infantAge = 2;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Integer()
     * @Assert\Type(type="numeric")
     * @ODM\Index()
     */
    private $position = 0;

    /**
     * @var Promotion[]|ArrayCollection
     * @ODM\ReferenceMany(targetDocument="Promotion")
     */
    protected $promotions;

    /**
     * @var Restriction[]|ArrayCollection
     * @ODM\ReferenceMany(targetDocument="Restriction", mappedBy="tariff", cascade={"remove"})
     */
    protected $restrictions;

    /**
     * @var PriceCache[]|ArrayCollection
     * @ODM\ReferenceMany(targetDocument="PriceCache", mappedBy="tariff", cascade={"remove"})
     */
    protected $priceCaches;

    /**
     * @var RoomCache[]|ArrayCollection
     * @ODM\ReferenceMany(targetDocument="RoomCache", mappedBy="tariff", cascade={"remove"})
     */
    protected $roomCaches;

    /**
     * @ODM\ReferenceOne(targetDocument="Promotion")
     * @var Promotion|null
     */
    protected $defaultPromotion;

    /**
     * @var Service[]|ArrayCollection
     * @ODM\ReferenceMany(targetDocument="Service")
     */
    protected $services;

    /**
     * @var TariffService[]
     * @ODM\EmbedMany(targetDocument="TariffService")
     */
    protected $defaultServices;

    /**
     * @var TariffChildOptions
     * @ODM\EmbedOne(targetDocument="TariffChildOptions")
     */
    protected $childOptions;

    /** Tariff[]
     * @ODM\ReferenceMany(targetDocument="Tariff", mappedBy="parent")
     */
    protected $children;

    /**
     * @var Tariff
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Tariff", inversedBy="children")
     */
    protected $parent;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="int", name="minPerPrepay")
     * @Assert\Range(min=0, max=100)
     */
    protected $minPerPrepay = 0;

    /**
     * @var Tariff
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Tariff")
     */
    protected $mergingTariff;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean(name="isOpen")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $isOpen = true;

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->isOpen;
    }

    /**
     * @param bool $isOpen
     */
    public function setIsOpen(bool $isOpen): void
    {
        $this->isOpen = $isOpen;
    }

    /**
     * Tariff constructor.
     */

    public function __construct()
    {
        $this->promotions = new ArrayCollection();
        $this->defaultServices = new ArrayCollection();
    }

    public function __clone()
    {
        parent::__clone();
        $this->isDefault = false;
        $this->promotions = new ArrayCollection();
        $this->defaultServices = new ArrayCollection();
        $this->services  = new ArrayCollection();
    }

    /**
     * Set hotel
     *
     * @param Hotel $hotel
     * @return self
     */
    public function setHotel(Hotel $hotel)
    {
        $this->hotel = $hotel;
        return $this;
    }

    /**
     * Get hotel
     *
     * @return \MBH\Bundle\HotelBundle\Document\Hotel $hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * Set fullTitle
     *
     * @param string $fullTitle
     * @return self
     */
    public function setFullTitle($fullTitle)
    {
        $this->fullTitle = $fullTitle;
        return $this;
    }

    /**
     * Get fullTitle
     *
     * @return string $fullTitle
     */
    public function getFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     * @return self
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean $isDefault
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set isOnline
     *
     * @param boolean $isOnline
     * @return self
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;
        return $this;
    }

    /**
     * Get isOnline
     *
     * @return boolean $isOnline
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set begin
     *
     * @param date $begin
     * @return self
     */
    public function setBegin($begin)
    {
        $this->begin = $begin;
        return $this;
    }

    /**
     * Get begin
     *
     * @return \DateTime $begin
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Set end
     *
     * @param date $end
     * @return self
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime $end
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set childAge
     *
     * @param int $childAge
     * @return self
     */
    public function setChildAge($childAge)
    {
        $this->childAge = $childAge;
        return $this;
    }

    /**
     * Get childAge
     *
     * @return int $childAge
     */
    public function getChildAge()
    {
        return $this->childAge;
    }

    /**
     * Set infantAge
     *
     * @param int $infantAge
     * @return self
     */
    public function setInfantAge($infantAge)
    {
        $this->infantAge = $infantAge;
        return $this;
    }

    /**
     * Get infantAge
     *
     * @return int $infantAge
     */
    public function getInfantAge()
    {
        return $this->infantAge;
    }

    /**
     * @return ArrayCollection|Promotion[]
     */
    public function getPromotions()
    {
        return $this->promotions;
    }

    /**
     * @param ArrayCollection|Promotion[] $promotions
     */
    public function setPromotions($promotions)
    {
        $this->promotions = $promotions;
    }

    /**
     * @return Promotion|null
     */
    public function getDefaultPromotion()
    {
        return $this->defaultPromotion;
    }

    /**
     * @param Promotion|null $defaultPromotion
     */
    public function setDefaultPromotion(Promotion $defaultPromotion = null)
    {
        $this->defaultPromotion = $defaultPromotion;
    }

    /**
     * @return ArrayCollection|Service[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param ArrayCollection|Service[] $services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }

    /**
     * @param TariffService $defaultService
     * @return $this
     */
    public function addDefaultService(TariffService $defaultService)
    {
        $this->defaultServices[] = $defaultService;
        return $this;
    }

    /**
     * @param Promotion $promotion
     * @return $this
     */
    public function addPromotion(Promotion $promotion)
    {
        $this->promotions[] = $promotion;
        return $this;
    }

    /**
     * @param Service $service
     * @return $this
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;
        return $this;
    }

    /**
     * @return TariffService[]
     */
    public function getDefaultServices()
    {
        return $this->defaultServices;
    }

    /**
     * @param TariffService[] $defaultServices
     */
    public function setDefaultServices($defaultServices)
    {
        $this->defaultServices = $defaultServices;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     * @return TariffChildOptions
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return TariffChildOptions
     */
    public function getChildOptions()
    {
        return $this->childOptions ?: new TariffChildOptions();
    }

    /**
     * @param mixed $childOptions
     * @return Tariff
     */
    public function setChildOptions($childOptions)
    {
        $this->childOptions = $childOptions;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     * @return Tariff
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @return Tariff
     */
    public function getMergingTariff(): ?Tariff
    {
        return $this->mergingTariff;
    }

    /**
     * @param Tariff $mergingTariff
     * @return Tariff
     */
    public function setMergingTariff(?Tariff $mergingTariff)
    {
        $this->mergingTariff = $mergingTariff;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRestrictions()
    {
        return $this->restrictions;
    }

    /**
     * @param mixed $restrictions
     * @return Tariff
     */
    public function setRestrictions($restrictions)
    {
        $this->restrictions = $restrictions;
        return $this;
    }

    /**
     * @return ArrayCollection|PriceCache[]
     */
    public function getPriceCaches()
    {
        return $this->priceCaches;
    }

    /**
     * @param ArrayCollection|PriceCache[] $priceCaches
     * @return Tariff
     */
    public function setPriceCaches($priceCaches)
    {
        $this->priceCaches = $priceCaches;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoomCaches()
    {
        return $this->roomCaches;
    }

    /**
     * @param mixed $roomCaches
     * @return Tariff
     */
    public function setRoomCaches($roomCaches)
    {
        $this->roomCaches = $roomCaches;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return Tariff
     */
    public function setPosition(int $position): Tariff
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinPerPrepay(): int
    {
        return $this->minPerPrepay ?? 0;
    }

    /**
     * @param int $minPerPrepay
     * @return $this
     */
    public function setMinPerPrepay(?int $minPerPrepay)
    {
        $this->minPerPrepay = $minPerPrepay;

        return $this;
    }

    /**
     * @param bool $isFull
     * @return array
     */
    public function getJsonSerialized($isFull = false)
    {
        $data = [
            'id' => $this->getId(),
            'title' => $this->getFullTitle() ?? $this->getTitle(),
            'description' => $this->getDescription() ?? '',
            'hotel' => $this->getHotel()->getId()
        ];
        if ($isFull) {
            $comprehensiveData = [
                'isEnabled' => $this->getIsEnabled(),
                'isDefault' => $this->getIsDefault(),
                'isOnline' => $this->getIsOnline(),
                'childAge' => $this->getChildAge(),
                'infantAge' => $this->getInfantAge(),
            ];

            if (!is_null($this->getParent())) {
                $comprehensiveData['parentTariffId'] = $this->getParent()->getId();
            }
            if (!is_null($this->getBegin())) {
                $comprehensiveData['begin'] = $this->getBegin()->format(ApiHandler::DATE_FORMAT);
            }
            if (!is_null($this->getEnd())) {
                $comprehensiveData['end'] = $this->getEnd()->format(ApiHandler::DATE_FORMAT);
            }
            $defaultServicesData = [];
            foreach ($this->defaultServices as $defaultService) {
                $defaultServicesData[] = [
                    'service' => $defaultService->getService()->getJsonSerialized(),
                    'amount' => $defaultService->getAmount(),
                    'persons' => $defaultService->getPersons(),
                    'nights' => $defaultService->getNights()
                ];
            }
            $comprehensiveData['defaultServices'] = $defaultServicesData;

            $availableServiceData = [];
            foreach ($this->getServices() as $service) {
                $availableServiceData[] = $service->getJsonSerialized();
            }
            $comprehensiveData['availableServices'] = $availableServiceData;

            $data = array_merge($data, $comprehensiveData);
        }

        return $data;
    }
}