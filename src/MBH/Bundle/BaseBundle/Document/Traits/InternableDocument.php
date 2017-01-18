<?php

namespace MBH\Bundle\BaseBundle\Document\Traits;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class InternableDocument

 */
trait InternableDocument
{
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.hotel.min_name",
     *      max=100,
     *      maxMessage="validator.document.hotel.min_name"
     * )
     * @Assert\Regex(pattern="/^[^А-Яа-я]+$/iu", message="validator.document.roomType.internationalTitle.only_english")
     */
    protected $internationalTitle;

    /**
     * @return string
     */
    public function getInternationalTitle()
    {
        return $this->internationalTitle;
    }

    /**
     * @param string $internationalTitle
     */
    public function setInternationalTitle($internationalTitle)
    {
        $this->internationalTitle = $internationalTitle;
    }

    public function getFullTitle()
    {
        return;
    }

    protected function getNationalLocale()
    {
        return 'ru';
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getLocaleTitle($locale = 'en')
    {
        if ($locale != $this->getNationalLocale() && $this->getInternationalTitle()) {
            return $this->getInternationalTitle();
        }

        return $this->getFullTitle();
    }
}