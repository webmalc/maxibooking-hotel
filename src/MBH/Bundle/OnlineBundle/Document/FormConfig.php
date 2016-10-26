<?php

namespace MBH\Bundle\OnlineBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * @ODM\Document(collection="FormConfig", repositoryClass="MBH\Bundle\OnlineBundle\Document\FormConfigRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ExclusionPolicy("all")
 */
class FormConfig extends Base
{
    const paymentTypesList = [
        "in_hotel", "online_full", "online_first_day", "online_half"
    ];

    const CSS = [
        'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' => 'bootstrap',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cerulean/bootstrap.min.css' => 'cerulean',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cosmo/bootstrap.min.css' => 'cosmo',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cyborg/bootstrap.min.css' => 'cyborg',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/darkly/bootstrap.min.css' => 'darkly',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/flatly/bootstrap.min.css' => 'flatly',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/journal/bootstrap.min.css' => 'journal',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/lumen/bootstrap.min.css' => 'lumen',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/paper/bootstrap.min.css' => 'paper',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/readable/bootstrap.min.css' =>  'readable',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/sandstone/bootstrap.min.css' => 'sandstone',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/simplex/bootstrap.min.css' => 'simplex',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/slate/bootstrap.min.css' => 'slate',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/spacelab/bootstrap.min.css' => 'spacelab',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/superhero/bootstrap.min.css' => 'superhero',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/united/bootstrap.min.css' => 'united',
        'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/yeti/bootstrap.min.css' => 'yeti',
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
     * @Assert\NotNull()
     * @Expose
     */
    protected $hotels;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     * @Expose
     */
    protected $enabled = true;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $roomTypes = true;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $tourists = true;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\NotNull()
     * @Assert\Type(type="boolean")
     */
    protected $nights = false;

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
     * @Assert\NotNull()
     * @Assert\Choice(callback = "getCssListKeys")
     */
    protected $css;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $style;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    protected $language;

    public function __construct()
    {
        $this->hotels = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return collection $paymentTypes
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
     * @return array
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
     * @return string
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * @param string $css
     * @return FormConfig
     */
    public function setCss($css): FormConfig
    {
        $this->css = $css;

        return $this;
    }


    /**
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @param string $style
     * @return FormConfig
     */
    public function setStyle($style): FormConfig
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return FormConfig
     */
    public function setLanguage(string $language): FormConfig
    {
        $this->language = $language;
        return $this;
    }



    public static function getCssList(): array
    {
        return self::CSS;
    }

    public static function getCssListKeys(): array
    {
        return array_keys(self::CSS);
    }

}
