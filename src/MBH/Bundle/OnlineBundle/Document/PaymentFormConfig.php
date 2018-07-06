<?php
/**
 * Created by PhpStorm.
 * Date: 31.05.18
 */

namespace MBH\Bundle\OnlineBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class FormPaymentConfig
 * @package MBH\Bundle\OnlineBundle\Document
 *
 * @ODM\Document(collection="PaymentFormConfig", repositoryClass="PaymentFormConfigRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class PaymentFormConfig extends Base implements DecorationInterface, DecorationDataInterface
{
    use DecorationTrait;
    use DecorationDataTrait;

    const WRAPPER_ID = 'mbh-payment-form-wrapper';

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
     * @var bool
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    private $enabledReCaptcha = false;

    /**
     * @var bool
     * @Gedmo\Versioned
     * @ODM\Field(type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    private $fieldUserNameIsVisible = false;

    /**
     * @var bool
     * @Gedmo\Versioned
     * @ODM\Field(type="bool")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    private $enabledShowAmount = false;

    /**
     * @var bool
     * @Gedmo\Versioned
     * @ODM\Field(type="bool")
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    private $enabledMaxAmountLimit = true;

    /**
     * @var string
     * @Gedmo\Versioned()
     * @ODM\Field(type="string")
     */
    private $js;

    /**
     * @return bool
     */
    public function isEnabledMaxAmountLimit(): bool
    {
        return $this->enabledMaxAmountLimit;
    }

    /**
     * @param bool $enabledMaxAmountLimit
     */
    public function setEnabledMaxAmountLimit(bool $enabledMaxAmountLimit)
    {
        $this->enabledMaxAmountLimit = $enabledMaxAmountLimit;
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
     * @return PaymentFormConfig
     */
    public function setHotels($hotels)
    {
        $this->hotels = $hotels;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabledShowAmount(): bool
    {
        return $this->enabledShowAmount;
    }

    /**
     * @param bool $enabledShowAmount
     */
    public function setEnabledShowAmount(bool $enabledShowAmount): void
    {
        $this->enabledShowAmount = $enabledShowAmount;
    }

    /**
     * @return bool
     */
    public function isFieldUserNameIsVisible(): bool
    {
        return $this->fieldUserNameIsVisible;
    }

    /**
     * @param bool $fieldUserNameIsVisible
     */
    public function setFieldUserNameIsVisible(bool $fieldUserNameIsVisible): void
    {
        $this->fieldUserNameIsVisible = $fieldUserNameIsVisible;
    }

    /**
     * @return bool
     */
    public function isEnabledReCaptcha(): bool
    {
        return $this->enabledReCaptcha;
    }

    /**
     * @param bool $enabledReCaptcha
     */
    public function setEnabledReCaptcha(bool $enabledReCaptcha): void
    {
        $this->enabledReCaptcha = $enabledReCaptcha;
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
     */
    public function setJs(string $js = null): void
    {
        $this->js = $js;
    }
}