<?php

namespace MBH\Bundle\ChannelManagerBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class BookingRoom extends Room
{
    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    private $uploadSinglePrices = true;

    /**
     * @return bool
     */
    public function isUploadSinglePrices(): ?bool
    {
        return $this->uploadSinglePrices;
    }

    /**
     * @param bool $uploadSinglePrices
     * @return BookingRoom
     */
    public function setUploadSinglePrices(bool $uploadSinglePrices): BookingRoom
    {
        $this->uploadSinglePrices = $uploadSinglePrices;

        return $this;
    }
}
