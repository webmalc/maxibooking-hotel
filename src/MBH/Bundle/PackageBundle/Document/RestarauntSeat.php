<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\Document(collection="RestarauntSeat")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 */
class RestarauntSeat extends Base
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

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Tourist", inversedBy="restarauntSeat")
     */
    protected $tourist;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Package", inversedBy="restarauntSeat")
     */
    protected $package;
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date", name="begin")
     * @Assert\NotNull(message= "validator.document.package.begin_not_specified")
     * @Assert\Date()
     */
    protected $begin;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date", name="end")
     * @Assert\NotNull(message= "validator.document.package.end_not_specified")
     * @Assert\Date()
     */
    protected $end;

}


