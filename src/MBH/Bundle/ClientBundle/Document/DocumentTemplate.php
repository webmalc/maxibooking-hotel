<?php

namespace MBH\Bundle\ClientBundle\Document;


use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Organization;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;


/**
 * Class DocumentTemplate

 *
 * @ODM\Document(collection="DocumentTemplate")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class DocumentTemplate extends Base
{
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;

    const ORIENTATION_PORTRAIT = 'portrait';
    const ORIENTATION_LANDSCAPE =  'landscape';

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     *
     * @Assert\NotNull()
     * @Assert\Type(type="string")
     */
    protected $title;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     *
     * @Assert\NotNull()
     * @Assert\Type(type="string")
     */
    protected $content;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     *
     * @Assert\NotNull()
     * @Assert\Choice(callback="getOrientations")
     */
    protected $orientation = self::ORIENTATION_PORTRAIT;

    /**
     * @var Hotel|null
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="\MBH\Bundle\HotelBundle\Document\Hotel")
     */
    protected $hotel;

    /**
     * @var Organization
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="\MBH\Bundle\PackageBundle\Document\Organization")
     */
    protected $organization;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * @param mixed $orientation
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;
    }

    /**
     * @return Hotel|null
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param Hotel|null $hotel
     */
    public function setHotel(Hotel $hotel = null)
    {
        $this->hotel = $hotel;
    }

    /**
     * @return array
     */
    public static function getOrientations()
    {
        return [
            self::ORIENTATION_PORTRAIT => self::ORIENTATION_PORTRAIT,
            self::ORIENTATION_LANDSCAPE => self::ORIENTATION_LANDSCAPE
        ];
    }

    /**
     * @return Organization|null
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization|null
     * @return DocumentTemplate
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;
        return $this;
    }

}