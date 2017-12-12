<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\BaseBundle\Document\Traits\HotelableDocument;
use MBH\Bundle\OnlineBundle\Form\InvitedCityType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Invite

 *
 * @ODM\Document()
 * @Gedmo\Loggable
 * @ODM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Invite extends Base implements \JsonSerializable
{
    const TYPE_SINGLE = 'single';
    const TYPE_TWICE = 'twice';

    use HotelableDocument;
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\Date()
     */
    protected $arrival;
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Field(type="date")
     * @Assert\Date()
     */
    protected $departure;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $type;
    /**
     * @var InvitedTourist[]
     * @ODM\EmbedMany(targetDocument="MBH\Bundle\OnlineBundle\Document\InvitedTourist")
     */
    protected $guests = [];

    /**
     * @ODM\EmbedMany(targetDocument="MBH\Bundle\OnlineBundle\Document\TripRoute")
     * @var TripRoute[]
     */
    protected $tripRoutes = [];

    public function __construct()
    {
        $this->guests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tripRoutes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getArrival()
    {
        return $this->arrival;
    }

    /**
     * @param \DateTime|null $arrival
     * @return $this
     */
    public function setArrival(\DateTime $arrival = null)
    {
        $this->arrival = $arrival;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeparture()
    {
        return $this->departure;
    }

    /**
     * @param \DateTime|null $departure
     * @return $this
     */
    public function setDeparture(\DateTime $departure = null)
    {
        $this->departure = $departure;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return InvitedTourist[]
     */
    public function getGuests()
    {
        return $this->guests;
    }
    /**
     * @param InvitedTourist $guest
     * @return $this
     */
    public function addGuest(InvitedTourist $guest)
    {
        $this->guests[] = $guest;
        return $this;
    }
    /**
     * @param InvitedTourist[] $guests
     * @return $this
     */
    public function setGuests($guests)
    {
        $this->guests = $guests;
        return $this;
    }

    /**
     * @return TripRoute[]
     */
    public function getTripRoutes()
    {
        return $this->tripRoutes;
    }

    /**
     * @param TripRoute[] $tripRoutes
     * @return $this
     */
    public function setTripRoutes($tripRoutes)
    {
        $this->tripRoutes = $tripRoutes;
        return $this;
    }

    /**
     * @param TripRoute $tripRoute
     * @return $this
     */
    public function addTripRoute(TripRoute $tripRoute)
    {
        $this->tripRoutes[] = $tripRoute;
        return $this;
    }


    public function jsonSerialize()
    {
        return [
            'arrival' => $this->getArrival() ? $this->getArrival()->format('d.m.Y') : null,
            'departure' => $this->getDeparture() ? $this->getDeparture()->format('d.m.Y') : null,
            'type' => $this->getType(),
            'hotel' => $this->getHotel(),
            'guests' => iterator_to_array($this->getGuests()),
            'tripRoutes' => iterator_to_array($this->getTripRoutes())
        ];
    }
}