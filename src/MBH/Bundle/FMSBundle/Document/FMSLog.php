<?php

namespace MBH\Bundle\FMSBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use MBH\Bundle\BaseBundle\Document\Base;

/**
 * Class FMS_Log
 * @package MBH\Bundle\FMSBundle\Document
 * @Gedmo\Loggable
 * @ODM\Document(collection="FMSLogs")
 */
class FMSLog extends Base
{
    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    protected $sendAt;

    /**
     * @return \DateTime
     */
    public function getSendAt(): \DateTime
    {
        return $this->sendAt;
    }

    /**
     * @param \DateTime $sendAt
     */
    public function setSendAt(\DateTime $sendAt)
    {
        $this->sendAt = $sendAt;
    }

    /**
     * @ODM\ReferenceMany(targetDocument="Package", nullable="true")
     */
    public $packages;

}