<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemCommonDocument;

/**
 * @ODM\EmbeddedDocument
 */
class Rbk extends PaymentSystemCommonDocument
{
    const COMMISSION = 0.039;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $rbkEshopId;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $rbkSecretKey;

    /**
     * @return string
     */
    public function getRbkEshopId()
    {
        return $this->rbkEshopId;
    }

    /**
     * @param $rbkEshopId
     * @return $this
     */
    public function setRbkEshopId($rbkEshopId)
    {
        $this->rbkEshopId = $rbkEshopId;

        return $this;
    }

    /**
     * @return string
     */
    public function getRbkSecretKey()
    {
        return $this->rbkSecretKey;
    }

    /**
     * @param $rbkSecretKey
     * @return $this
     */
    public function setRbkSecretKey($rbkSecretKey)
    {
        $this->rbkSecretKey = $rbkSecretKey;

        return $this;
    }
}
