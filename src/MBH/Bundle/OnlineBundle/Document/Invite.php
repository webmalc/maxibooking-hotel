<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Blameable\Traits\BlameableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\HotelableDocument;
use MBH\Bundle\OnlineBundle\Form\InvitedCityType;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Invite
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 *
 * @ODM\Document()
 * @Gedmo\Loggable
 * @ODM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Invite extends Base
{
    use HotelableDocument;
    use TimestampableDocument;
    use SoftDeleteableDocument;
    use BlameableDocument;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date
     * @Assert\Date()
     */
    protected $arrival;
    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date
     * @Assert\Date()
     */
    protected $departure;
    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $type;
    /**
     * @ODM\EmbedMany(targetDocument="MBH\Bundle\OnlineBundle\Document\InvitedTourist")
     * @var InvitedTourist[]
     */
    protected $guests;

    /**
     * @ODM\EmbedMany(targetDocument="MBH\Bundle\OnlineBundle\Document\TripRoute")
     * @var TripRoute[]
     */
    protected $tripRoutes = [];

    /**
     * @return mixed
     */
    public function getArrival()
    {
        return $this->arrival;
    }

    /**
     * @param \DateTime|null $arrival
     */
    public function setArrival(\DateTime $arrival = null)
    {
        $this->arrival = $arrival;
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
     */
    public function setDeparture($departure = null)
    {
        $this->departure = $departure;
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
     */
    public function setType($type)
    {
        $this->type = $type;
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
     */
    public function addGuest(InvitedTourist $guest)
    {
        $this->guests[] = $guest;
    }
    /**
     * @param InvitedTourist[] $guests
     */
    public function setGuests($guests)
    {
        $this->guests = $guests;
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
     */
    public function setTripRoutes($tripRoutes)
    {
        $this->tripRoutes = $tripRoutes;
    }

    /**
     * @param TripRoute $tripRoute
     */
    public function addTripRoute(TripRoute $tripRoute)
    {
        $this->tripRoutes[] = $tripRoute;
    }
}