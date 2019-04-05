<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Annotations as MBH;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService;
use MBH\Bundle\BaseBundle\Service\Messenger\RecipientInterface;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\PackageBundle\Document\Partials\DeleteReasonTrait;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use MBH\Bundle\UserBundle\Lib\OwnerInterface;
use MBH\Bundle\UserBundle\Lib\OwnerTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Order", repositoryClass="MBH\Bundle\PackageBundle\Document\OrderRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 */
class Order extends Base implements OwnerInterface
{
    const OFFLINE_STATUS = 'offline';
    const ONLINE_STATUS = 'online';
    const CHANNEL_MANAGER_STATUS = 'channel_manager';

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
     * Delete Reason Trait
     */
    use DeleteReasonTrait;

    use OwnerTrait;

    /**
     * @var int
     * @ODM\Id(strategy="INCREMENT")
     */
    protected $id;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="PackageSource", inversedBy="orders")
     */
    protected $source;

    /**
     * @ODM\ReferenceMany(targetDocument="Package", mappedBy="order")
     */
    protected $packages;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Tourist", inversedBy="orders")
     */
    protected $mainTourist;

    /**
     * @ODM\ReferenceOne(targetDocument="Organization")
     */
    protected $organization;

    /** 
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\CashBundle\Document\CashDocument", mappedBy="order")
     * @MBH\Versioned()
     */
    protected $cashDocuments;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.order.price_min_message"
     * )
     * @ODM\Index()
     */
    protected $price;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.order.price_less_zero"
     * )
     * @ODM\Index()
     */
    protected $originalPrice;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.package.price_less_zero"
     * )
     * @ODM\Index()
     */
    protected $totalOverwrite;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Field(type="float")
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.order.payed_sum_min_message"
     * )
     * @ODM\Index()
     */
    protected $paid = 0;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     * @ODM\Index()
     */
    protected $isPaid = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\Type(type="boolean")
     * @ODM\Index()
     */
    protected $confirmed = false;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="status")
     * @Assert\Choice(
     *      callback = "getStatusesList",
     *      message = "validator.document.order.wrong_status"
     * )
     * @ODM\Index()
     */
    protected $status;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="onlinePaymentType")
     * @Assert\Choice(
     *      callback = "getOnlinePaymentTypesList",
     *      message = "validator.document.package.wrong_online_payment_type"
     * )
     */
    protected $onlinePaymentType;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="channelManagerType")
     * @Assert\Choice(
     *      callback="getChannelManagerNames",
     *      message = "validator.document.package.wrong_channel_manager_type"
     * )
     * @ODM\Index()
     */
    protected $channelManagerType;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="channelManagerId")
     * @ODM\Index()
     */
    protected $channelManagerId;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $channelManagerHumanId;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $channelManagerHumanText;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $channelManagerEditDateTime;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $channelManagerStatus;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="note")
     */
    protected $note;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $card;

    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="OrderPollQuestion")
     */
    protected $pollQuestions;

    /**
     * @var CreditCard
     * @ODM\EmbedOne(targetDocument="CreditCard")
     */
    protected $creditCard;

    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="OrderDocument")
     */
    protected $documents = [];

    public function __construct()
    {
        $this->packages = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->cashDocuments = new ArrayCollection();
    }

    public static function getOnlinePaymentTypesList()
    {
        return FormConfig::paymentTypesList;
    }

    /**
     * Add package
     *
     * @param \MBH\Bundle\PackageBundle\Document\Package $package
     */
    public function addPackage(\MBH\Bundle\PackageBundle\Document\Package $package)
    {
        $this->packages[] = $package;
    }

    /**
     * Remove package
     *
     * @param \MBH\Bundle\PackageBundle\Document\Package $package
     */
    public function removePackage(\MBH\Bundle\PackageBundle\Document\Package $package)
    {
        $this->packages->removeElement($package);
    }

    /**
     * Get packages
     *
     * @return Package[]|ArrayCollection $packages
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @return ArrayCollection
     */
    public function getPackagesSortedByNumber()
    {
        $packages = $this->packages->toArray();
        usort($packages, function ($a, $b) {
            /** @var Package $a */
            /** @var Package $b */
            return ($a->getNumber() < $b->getNumber()) ? -1 : 1;
        });

        return new ArrayCollection($packages);
    }

    /**
     * Set mainTourist
     *
     * @param \MBH\Bundle\PackageBundle\Document\Tourist $mainTourist
     * @return self
     */
    public function setMainTourist(\MBH\Bundle\PackageBundle\Document\Tourist $mainTourist = null)
    {
        $this->mainTourist = $mainTourist;
        return $this;
    }

    /**
     * Get mainTourist
     *
     * @return \MBH\Bundle\PackageBundle\Document\Tourist|null $mainTourist
     */
    public function getMainTourist()
    {
        return $this->mainTourist;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;
    }

    /**
     * Set price
     *
     * @param int $price
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     * @param boolean $isFloat
     * @return float $price
     */
    public function getPrice($isFloat = false)
    {
        if (!empty($this->getTotalOverwrite())) {
            return $this->getTotalOverwrite();
        }

        if ($isFloat) {
            return number_format((float) $this->price, 2, '.', '');
        }
        return $this->price;
    }

    /**
     * Set paid
     *
     * @param int $paid
     * @return self
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;
        return $this;
    }

    /**
     * Get paid
     *
     * @return int $paid
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set isPaid
     *
     * @param boolean $isPaid
     * @return self
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;
        return $this;
    }

    /**
     * Get isPaid
     *
     * @return boolean $isPaid
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     * @return self
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
        return $this;
    }

    /**
     * Get confirmed
     *
     * @return boolean $confirmed
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return self
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Get note
     *
     * @return string $note
     */
    public function getNote()
    {
        return $this->note;
    }

    public function calcPrice(Package $excludePackage = null)
    {
        $this->price = 0;

        foreach ($this->getPackages() as $package) {

            if ($package->getDeletedAt()) {
                continue;
            }

            if (empty($excludePackage) || $excludePackage->getId() != $package->getId()) {
                $this->price += $package->getPrice();
            }
        }
        return $this;
    }

    /**
     * Add cashDocument
     *
     * @param \MBH\Bundle\CashBundle\Document\CashDocument $cashDocument
     */
    public function addCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument)
    {
        $this->cashDocuments[] = $cashDocument;
    }

    /**
     * Remove cashDocument
     *
     * @param \MBH\Bundle\CashBundle\Document\CashDocument $cashDocument
     */
    public function removeCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument)
    {
        $this->cashDocuments->removeElement($cashDocument);
    }

    /**
     * Get cashDocuments
     *
     * @return \Doctrine\Common\Collections\Collection|CashDocument[] $cashDocuments
     */
    public function getCashDocuments()
    {
        return $this->cashDocuments;
    }

    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->checkPaid();
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->checkPaid();
    }

    public function checkPaid()
    {
        if ($this->getPaid() >= $this->getPrice()) {
            $this->setIsPaid(true);
        } else {
            $this->setIsPaid(false);
        }
    }

    /**
     * Set source
     *
     * @param \MBH\Bundle\PackageBundle\Document\PackageSource $source
     * @return self
     */
    public function setSource(\MBH\Bundle\PackageBundle\Document\PackageSource $source = null)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Get source
     *
     * @return \MBH\Bundle\PackageBundle\Document\PackageSource $source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set card
     * @deprecated
     * @param string $card
     * @return self
     */
    public function setCard($card)
    {
        $this->card = $card;
        return $this;
    }

    /**
     * Get card
     * @deprecated
     * @return string $card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Set channelMangerHumanId
     *
     * @param string $channelManagerHumanId
     * @return self
     */
    public function setChannelManagerHumanId($channelManagerHumanId)
    {
        $this->channelManagerHumanId = $channelManagerHumanId;
        return $this;
    }

    /**
     * Get channelManagerHumanId
     *
     * @return string channelManagerHumanId
     */
    public function getChannelManagerHumanId()
    {
        return $this->channelManagerHumanId;
    }

    /**
     * Set channelManagerType
     *
     * @param string $channelManagerType
     * @return self
     */
    public function setChannelManagerType($channelManagerType)
    {
        $this->channelManagerType = $channelManagerType;
        return $this;
    }



    /**
     * Get channelManagerType
     *
     * @return string $channelManagerType
     */
    public function getChannelManagerType()
    {
        return $this->channelManagerType;
    }

    /**
     * Set channelManagerId
     *
     * @param string $channelManagerId
     * @return self
     */
    public function setChannelManagerId($channelManagerId)
    {
        $this->channelManagerId = $channelManagerId;
        return $this;
    }

    /**
     * Get channelManagerId
     *
     * @return string $channelManagerId
     */
    public function getChannelManagerId()
    {
        return $this->channelManagerId;
    }

    /**
     * Set totalOverwrite
     *
     * @param float $totalOverwrite
     * @return self
     */
    public function setTotalOverwrite($totalOverwrite)
    {
        $this->totalOverwrite = $totalOverwrite;
        return $this;
    }

    /**
     * Get totalOverwrite
     *
     * @return float $totalOverwrite
     */
    public function getTotalOverwrite()
    {
        return $this->totalOverwrite;
    }

    public function removeAllPackages()
    {
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set $channelManagerStatus
     *
     * @param string $channelManagerStatus
     * @return self
     */
    public function setChannelManagerStatus($channelManagerStatus)
    {
        $this->channelManagerStatus = $channelManagerStatus;
        return $this;
    }

    /**
     * Get channelMangerHumanId
     *
     * @return string $channelManagerStatus
     */
    public function getChannelManagerStatus()
    {
        return $this->channelManagerStatus;
    }

    /**
     * Add document
     *
     * @param \MBH\Bundle\PackageBundle\Document\OrderDocument $document
     */
    public function addDocument(\MBH\Bundle\PackageBundle\Document\OrderDocument $document)
    {
        $this->documents[] = $document;
    }

    /**
     * Remove document
     *
     * @param \MBH\Bundle\PackageBundle\Document\OrderDocument $document
     */
    public function removeDocument(\MBH\Bundle\PackageBundle\Document\OrderDocument $document)
    {
        $this->documents->removeElement($document);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection|OrderDocument[] $documents
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param string $name
     * @return OrderDocument|null
     */
    public function getDocument($name)
    {
        foreach ($this->getDocuments() as $doc) {
            if ($doc->getName() == $name) {
                return $doc;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return Order
     */
    public function removeDocumentByName($name)
    {
        $doc = $this->getDocument($name);

        if ($doc) {
            $this->removeDocument($doc);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPaidStatus()
    {
        if ($this->getIsPaid()) {
            return 'success';
        }
        if ($this->getPaid() && !$this->getIsPaid()) {
            return 'warning';
        }
        if (!$this->getPaid()) {
            return 'danger';
        }

        return null;
    }

    /**
     * @return float
     */
    public function getDebt()
    {
        return $this->getPrice() - $this->getPaid();
    }

    /**
     * @return RecipientInterface|PayerInterface|null
     */
    public function getPayer()
    {
        if ($this->getOrganization()) {
            return $this->getOrganization();
        } elseif ($this->getMainTourist()) {
            return $this->getMainTourist();
        } else {
            null;
        }
    }

    /**
     * @return array
     */
    public function getFee()
    {
        if (empty($this->getCashDocuments())) {
            return [];
        }

        $fee = [];
        foreach($this->getCashDocuments() as $doc) {
            if ($doc->getOperation() == 'fee') {
                $fee[] = $doc;
            }
        }

        return $fee;
    }

    /**
     * @return \MBH\Bundle\HotelBundle\Document\Hotel;
     */
    public function getFirstHotel()
    {
        $package = $this->getFirstPackage();

        return $package ? $package->getHotel() : null;
    }

    /**
     * @return Package;
     */
    public function getFirstPackage()
    {
        foreach ($this->getPackages() as $package) {
            if (!$package->getDeletedAt()) {
                return $package;
            }
        }

        return null;
    }

    /**
     * Add pollQuestion
     *
     * @param \MBH\Bundle\PackageBundle\Document\OrderPollQuestion $pollQuestion
     */
    public function addPollQuestion(\MBH\Bundle\PackageBundle\Document\OrderPollQuestion $pollQuestion)
    {
        $this->pollQuestions[] = $pollQuestion;
    }

    /**
     * Remove pollQuestion
     *
     * @param \MBH\Bundle\PackageBundle\Document\OrderPollQuestion $pollQuestion
     */
    public function removePollQuestion(\MBH\Bundle\PackageBundle\Document\OrderPollQuestion $pollQuestion)
    {
        $this->pollQuestions->removeElement($pollQuestion);
    }

    /**
     * Get pollQuestions
     * @param boolean $grouped
     * @return \Doctrine\Common\Collections\Collection $pollQuestions
     */
    public function getPollQuestions($grouped = false)
    {
        if (!$grouped) {
            return $this->pollQuestions;
        }

        $result = [
            'questions' => [],
            'other' => []
        ];

        foreach ($this->pollQuestions as $pollQuestion) {
            $question = $pollQuestion->getQuestion();
            if ($question && $pollQuestion->getIsQuestion()) {
                $result['questions'][$question->getCategory()][] = $pollQuestion;
            } else {
                $result['other'][] = $pollQuestion;
            }
        }

        return $result;
    }

    /**
     * @return $this
     */
    public function removeAllPollQuestions()
    {
        $this->pollQuestions = new \Doctrine\Common\Collections\ArrayCollection();

        return $this;
    }

    /**
     * @return int
     */
    public function getOriginalPrice()
    {
        return $this->originalPrice ? $this->originalPrice : $this->getPrice();
    }

    /**
     * @param int $originalPrice
     * @return self
     */
    public function setOriginalPrice($originalPrice)
    {
        $this->originalPrice = $originalPrice;

        return $this;
    }

    /**
     * @return CreditCard
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * @param CreditCard $creditCard
     */
    public function setCreditCard(CreditCard $creditCard = null)
    {
        $this->creditCard = $creditCard;
    }

    /**
     * @return string
     */
    public function getChannelManagerHumanText()
    {
        return $this->channelManagerHumanText;
    }

    /**
     * @param string $channelManagerHumanText
     * @return Order
     */
    public function setChannelManagerHumanText($channelManagerHumanText)
    {
        $this->channelManagerHumanText = $channelManagerHumanText;
        return $this;
    }

    /**
     * @return string
     */
    public function getChannelManagerEditDateTime()
    {
        return $this->channelManagerEditDateTime;
    }

    /**
     * @param string $channelManagerEditDateTime
     * @return Order
     */
    public function setChannelManagerEditDateTime($channelManagerEditDateTime)
    {
        $this->channelManagerEditDateTime = $channelManagerEditDateTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getOnlinePaymentType()
    {
        return $this->onlinePaymentType;
    }

    /**
     * @param string $onlinePaymentType
     * @return Order
     */
    public function setOnlinePaymentType($onlinePaymentType)
    {
        $this->onlinePaymentType = $onlinePaymentType;
        return $this;
    }

    public static function getChannelManagerNames()
    {
        return AbstractChannelManagerService::getChannelManagerNames();
    }

    /**
     * @return array
     */
    public static function getStatusesList()
    {
        return [
            self::CHANNEL_MANAGER_STATUS,
            self::OFFLINE_STATUS,
            self::ONLINE_STATUS
        ];
    }


    /**
     * @return array
     */
    public function getJsonSerialized()
    {
        $packages = array_map(function(Package $package) {
            return $package->getJsonSerialized();
        }, $this->getPackages()->toArray());

        return [
            'id' => $this->getId(),
            'note' => $this->getNote(),
            'mainTourist' => $this->getMainTourist()->jsonSerialize(),
            'price' => $this->getPrice(),
            'packages' => $packages
        ];
    }
}
