<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 23.03.17
 * Time: 12:54
 */

namespace MBH\Bundle\OnlineBookingBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LeftRoom
 * @package MBH\Bundle\OnlineBookingBundle\Document
 * @ODM\Document(collection="LeftRooms")
 * @UniqueEntity("key")
 */

class LeftRoom extends Base
{
    /**
     * @var string
     * @ODM\Field(type="string")
     * @Assert\NotNull()
     */
    protected $key;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Assert\NotNull()
     *
     */
    protected $date;

    /**
     * @var int
     * @ODM\Field(type="int")
     * @Assert\NotNull()
     *
     */
    protected $count;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;

    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count)
    {
        $this->count = $count;

        return $this;
    }

}