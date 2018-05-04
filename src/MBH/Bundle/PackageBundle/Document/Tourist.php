<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Lib\Exportable;
use MBH\Bundle\BaseBundle\Service\Messenger\RecipientInterface;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Partials\InnTrait;
use MBH\Bundle\PackageBundle\Lib\DataOfMortalInterface;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="Tourists", repositoryClass="MBH\Bundle\PackageBundle\Document\TouristRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 */
class Tourist extends Base implements \JsonSerializable, PayerInterface, RecipientInterface, Exportable, DataOfMortalInterface
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

    use InnTrait;

    /**
     * @ODM\ReferenceMany(targetDocument="Order", nullable="true", mappedBy="mainTourist")
     */
    public $orders;
    /**
     * @ODM\ReferenceMany(targetDocument="Package", nullable="true", mappedBy="tourists")
     */
    public $packages;
    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\CashBundle\Document\CashDocument", mappedBy="touristPayer")
     */
    protected $cashDocuments;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="firstName")
     * @Assert\NotNull(message= "validator.document.Tourist.name_required")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_name",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_name"
     * )
     * @ODM\Index()
     */
    protected $firstName;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="lastName")
     * @Assert\NotNull(message= "validator.document.Tourist.surname_required")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_surname",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_surname"
     * )
     * @ODM\Index()
     */
    protected $lastName;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="patronymic")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_second_name",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_second_name"
     * )
     * @ODM\Index()
     */
    protected $patronymic;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="fullName")
     * @ODM\Index()
     */
    protected $fullName;
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @ODM\Index
     * @Assert\Date()
     */
    protected $birthday;
    /**
     * @var \string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="sex")
     * @Assert\Choice(
     *      choices = {"male", "female", "unknown"},
     *      message =  "validator.document.Tourist.wrong_gender"
     * )
     */
    protected $sex = 'unknown';
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="phone")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_phone",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_phone"
     * )
     * @ODM\Index()
     */
    protected $phone;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_phone",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_phone"
     * )
     * @ODM\Index()
     */
    protected $mobilePhone;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $messenger;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="email")
     * @Assert\Email()
     * @ODM\Index()
     */
    protected $email;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="note")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_note",
     *      max=1000,
     *      maxMessage= "validator.document.Tourist.max_note"
     * )
     * @ODM\Index()
     */
    protected $note;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $citizenshipTld;
    /**
     * @ODM\EmbedOne(targetDocument="BirthPlace")
     * @var BirthPlace
     */
    protected $birthplace;
    /**
     * @var AddressObjectDecomposed
     * @ODM\EmbedOne(targetDocument="AddressObjectDecomposed")
     */
    protected $addressObjectDecomposed;
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $addressObjectCombined;
    /**
     * @var DocumentRelation
     * @ODM\EmbedOne(targetDocument="DocumentRelation")
     */
    protected $documentRelation;
    /**
     * @var Migration|null
     * @ODM\EmbedOne(targetDocument="Migration")
     */
    protected $migration;
    /**
     * @var Visa|null
     * @ODM\EmbedOne(targetDocument="Visa")
     */
    protected $visa;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @ODM\Index()
     */
    protected $communicationLanguage;

    /**
     * @var bool
     * @ODM\Field(type="boolean")
     * @ODM\Index()
     */
    protected $isUnwelcome = false;

    /**
     * @ODM\ReferenceMany(targetDocument="RestarauntSeat", mappedBy="tourist")
     */
    protected $restarauntSeat;

    public function __construct()
    {
        $this->restarauntSeat = new ArrayCollection();
        $this->packages = new ArrayCollection();
    }

    public function getShortName()
    {
        return $this->getLastNameWithInitials();
    }

    public function getLastNameWithInitials()
    {
        $result = $this->getLastName();

        if (!empty($this->getFirstName())) {
            $result .= ' ' . mb_substr($this->getFirstName(), 0, 1) . '.';
        }

        if (!empty($this->getPatronymic())) {
            $result .= mb_substr($this->getPatronymic(), 0, 1) . '.';
        }

        return $result;
    }

    /**
     * Get lastName
     *
     * @return string $lastName
     */
    public function getLastName()
    {
        if ($this->lastName == 'н/д') {
            return '';
        }

        return mb_convert_case(mb_strtolower($this->lastName), MB_CASE_TITLE);
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = mb_convert_case(mb_strtolower($lastName), MB_CASE_TITLE);

        if (empty($this->lastName)) {
            $this->lastName = null;
        }

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string $firstName
     */
    public function getFirstName()
    {
        if ($this->firstName == 'н/д') {
            return '';
        }

        return mb_convert_case(mb_strtolower($this->firstName), MB_CASE_TITLE);
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = mb_convert_case(mb_strtolower($firstName), MB_CASE_TITLE);

        if (empty($this->firstName)) {
            $this->firstName = null;
        }
        return $this;
    }

    /**
     * Get patronymic
     *
     * @return string $patronymic
     */
    public function getPatronymic()
    {
        return mb_convert_case(mb_strtolower($this->patronymic), MB_CASE_TITLE);
    }

    /**
     * Set patronymic
     *
     * @param string $patronymic
     * @return self
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = mb_convert_case(mb_strtolower($patronymic), MB_CASE_TITLE);

        if (empty($this->patronymic)) {
            $this->patronymic = null;
        }

        return $this;
    }

    /**
     * @return string $sex
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set sex
     *
     * @param string $sex
     * @return self
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
        return $this;
    }

    /**
     * Get address
     *
     * @return string $address
     */
    public function getAddress()
    {
        return $this->getAddressObjectCombined();
    }

    /**
     * @return string
     */
    public function getAddressObjectCombined()
    {
        return $this->addressObjectCombined;
    }

    /**
     * @param string $addressObjectCombined
     */
    public function setAddressObjectCombined($addressObjectCombined)
    {
        $this->addressObjectCombined = $addressObjectCombined;
    }

    /**
     * Set document
     *
     * @param string $document
     * @return self
     */
    public function setDocument($document)
    {
        $this->document = $document;
        return $this;
    }

    /**
     * Get document
     *
     * @return string $document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Get phone
     * @param boolean $original
     * @return string $phone
     */
    public function getPhone($original = false)
    {
        return self::formatPhone($this->phone, $original);
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = self::cleanPhone($phone);
        return $this;
    }

    /**
     * Returns formatted phone number
     * @param string $phone
     * @param boolean $original
     * @return string
     */
    public static function formatPhone($phone, $original = false)
    {
        $phone = self::cleanPhone($phone);

        if ($original || strlen($phone) < 7) {
            return $phone;
        } else {
            return empty($phone) ? null : '+ ' . substr($phone, 0, strlen($phone) - 7) . ' ' .
                substr($phone, -7, 3) . '-' .
                substr($phone, -4, 2) . '-' .
                substr($phone, -2, 2);
        }
    }

    /**
     * Cleans phone numbers
     * @param $phone
     * @return string
     */
    public static function cleanPhone($phone)
    {
        return preg_replace("/[^0-9]/", "", $phone);
    }

    /**
     * @param boolean $original
     * @return string
     */
    public function getMobilePhone($original = false)
    {
        return self::formatPhone($this->mobilePhone, $original);
    }

    /**
     * @param string $mobilePhone
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = self::cleanPhone($mobilePhone);;
    }

    /**
     * @return string
     */
    public function getMessenger()
    {
        return $this->messenger;
    }

    /**
     * @param string $messenger
     */
    public function setMessenger($messenger)
    {
        $this->messenger = $messenger;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        if (empty($this->sex) || $this->sex == 'unknown') {
            $this->sex = $this->guessGender();
        }

        $this->fullName = $this->generateFullName();
    }

    /**
     * Gender guess
     * @return "unknown"|"male"|"female"
     */
    public function guessGender()
    {
        $end = mb_substr($this->getPatronymic(), -2, 2, 'UTF-8');

        $gender = 'unknown';
        if ($end == 'ич' || $end == 'лы') {
            $gender = 'male';
        }
        if ($end == 'на' || $end == 'зы') {
            $gender = 'female';
        }

        return $gender;
    }

    /**
     * @return string
     */
    public function generateFullName()
    {
        $name = $this->getLastName() . ' ' . $this->getFirstName();

        return (empty($this->getPatronymic())) ? $name : $name . ' ' . $this->getPatronymic();
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        if (empty($this->sex) || $this->sex == 'unknown') {
            $this->sex = $this->guessGender();
        }

        $this->fullName = $this->generateFullName();
    }

    /**
     * Get birthday
     *
     * @return \DateTime|null $birthday
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     * @return self
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @param \DateTime|null $date
     * @return int|null
     */
    public function getAge(\DateTime $date = null)
    {
        if ($this->getBirthday()) {
            $now = $date ?? new \DateTime();
            $diff = $now->diff($this->getBirthday());

            return $diff->y;
        }

        return null;
    }

    /**
     * Add package
     *
     * @param Package $package
     */
    public function addPackage(Package $package)
    {
        $this->packages[] = $package;
    }

    /**
     * Remove package
     *
     * @param Package $package
     */
    public function removePackage(Package $package)
    {
        $this->packages->removeElement($package);
    }

    /**
     * Get packages
     *
     * @return \Doctrine\Common\Collections\Collection $packages
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getFullName();
    }

    /**
     * @return string $fullName
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return self
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullName();
    }

    /**
     * Add cashDocument
     *
     * @param CashDocument $cashDocument
     */
    public function addCashDocument(CashDocument $cashDocument)
    {
        $this->cashDocuments[] = $cashDocument;
    }

    /**
     * Remove cashDocument
     *
     * @param CashDocument $cashDocument
     */
    public function removeCashDocument(CashDocument $cashDocument)
    {
        $this->cashDocuments->removeElement($cashDocument);
    }

    /**
     * Get cashDocuments
     *
     * @return \Doctrine\Common\Collections\Collection $cashDocuments
     */
    public function getCashDocuments()
    {
        return $this->cashDocuments;
    }

    /**
     * Add order
     *
     * @param Order $order
     */
    public function addOrder(Order $order)
    {
        $this->orders[] = $order;
    }

    /**
     * Remove order
     *
     * @param Order $order
     */
    public function removeOrder(Order $order)
    {
        $this->orders->removeElement($order);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection $orders
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return string
     */
    public function getCitizenshipTld()
    {
        return $this->citizenshipTld;
    }

    /**
     * @param string $citizenshipTld
     */
    public function setCitizenshipTld(string $citizenshipTld = null)
    {
        $this->citizenshipTld = $citizenshipTld;
    }

    /**
     * @return BirthPlace
     */
    public function getBirthplace()
    {
        return $this->birthplace;
    }

    /**
     * @param BirthPlace $birthplace
     */
    public function setBirthplace(BirthPlace $birthplace = null)
    {
        $this->birthplace = $birthplace;
    }

    /**
     * @return AddressObjectDecomposed
     */
    public function getAddressObjectDecomposed()
    {
        return $this->addressObjectDecomposed;
    }

    /**
     * @param AddressObjectDecomposed $addressObjectDecomposed
     */
    public function setAddressObjectDecomposed(AddressObjectDecomposed $addressObjectDecomposed = null)
    {
        $this->addressObjectDecomposed = $addressObjectDecomposed;
    }

    /**
     * @return DocumentRelation
     */
    public function getDocumentRelation()
    {
        return $this->documentRelation;
    }

    /**
     * @param DocumentRelation $documentRelation
     */
    public function setDocumentRelation(DocumentRelation $documentRelation = null)
    {
        $this->documentRelation = $documentRelation;
    }

    /**
     * @return Migration|null
     */
    public function getMigration()
    {
        return $this->migration;
    }

    /**
     * @param Migration $migration
     */
    public function setMigration(Migration $migration = null)
    {
        $this->migration = $migration;
    }

    /**
     * @return Visa|null
     */
    public function getVisa()
    {
        return $this->visa;
    }

    /**
     * @param Visa $visa
     */
    public function setVisa(Visa $visa = null)
    {
        $this->visa = $visa;
    }

    /**
     * @return string
     */
    public function getCommunicationLanguage()
    {
        return $this->communicationLanguage;
    }

    /**
     * @param string $communicationLanguage
     */
    public function setCommunicationLanguage($communicationLanguage)
    {
        $this->communicationLanguage = $communicationLanguage;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsUnwelcome()
    {
        return $this->isUnwelcome;
    }

    /**
     * @param boolean $isUnwelcome
     */
    public function setIsUnwelcome($isUnwelcome)
    {
        $this->isUnwelcome = $isUnwelcome;
    }

    public function jsonSerialize()
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'patronymic' => $this->patronymic,
            'birthday' => $this->birthday ? $this->birthday->format('d.m.Y') : null,
            'phone' => $this->phone,
            'email' => $this->email,
            'communicationLanguage' => $this->communicationLanguage,
            'citizenshipTld' => $this->getCitizenshipTld() ? $this->getCitizenshipTld() : null,
            'documentRelation' => $this->getDocumentRelation() ? $this->getDocumentRelation() : null
        ];
    }

    public static function getExportableFieldsData(): array
    {
        return [
            'form.touristType.name' => ['field' => 'firstName'],
            'form.touristType.surname' => ['field' => 'lastName'],
            'form.touristType.second_name' => ['field' => 'patronymic'],
            'form.organizationType.phone' => ['field' => 'phone'],
            'form.touristType.mobile_phone' => ['field' => 'mobilePhone'],
            'form.touristType.email' => ['field' => 'email'],
            'exportable.tourist.fio' => [
                'callback' => function ($entityData) {
                    $fio = $entityData['lastName'] . ' ' . $entityData['firstName'];
                    if (isset($entityData['patronymic']) && !empty($entityData['patronymic'])) {
                        $fio .= ' ' . $entityData['patronymic'];
                    }
                    return $fio;
                }]
        ];
    }
}