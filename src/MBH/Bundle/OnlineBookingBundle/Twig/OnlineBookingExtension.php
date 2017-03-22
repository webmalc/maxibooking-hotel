<?php

namespace MBH\Bundle\OnlineBookingBundle\Twig;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\OnlineBookingBundle\Document\LeftRoom;

class OnlineBookingExtension extends \Twig_Extension
{
    /** @var DocumentManager */
    private $dm;

    const MIN_THRESHOLD = 3;

    const MAX_THRESHOLD = 7;

    /** @var  int */
    protected $minThreshold;

    /** @var  int */
    protected $maxThreshold;

    /**
     * OnlineBookingExtension constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm, array $thresholds = null)
    {
        $this->dm = $dm;
        if (!$thresholds) {
            $this->minThreshold = self::MIN_THRESHOLD;
            $this->maxThreshold = self::MAX_THRESHOLD;
        } else {
            $this->minThreshold = $thresholds['min_threshold'];
            $this->maxThreshold = $thresholds['max_threshold'];
        }
    }



    public function getFunctions()
    {
        return [
            'left_sale' => new \Twig_SimpleFunction('left_sale', [$this, 'leftSale'], array('is_safe' => array('html')))
        ];
    }

    public function leftSale(int $actualRoomsCount = null, string $leftRoomKey = null)
    {
        if (!$actualRoomsCount || !$leftRoomKey) {
            return 0;
        }

        $maxOutput = min($this->maxThreshold, $actualRoomsCount);
        if ($maxOutput < $this->maxThreshold) {
            return $maxOutput;
        }

        $leftRoom = $this->dm->getRepository('MBHOnlineBookingBundle:LeftRoom')->findOneBy(
            [
                'key' => $leftRoomKey,
            ]
        );

        $now = new \DateTime("now");
        $start = rand($this->minThreshold, $this->maxThreshold);
        if (!$leftRoom) {
            $leftRoom = new LeftRoom();
            $leftRoom
                ->setKey($leftRoomKey)
                ->setDate($now)
                ->setCount($start);
        }

        $interval = date_diff($now, $leftRoom->getDate(), true);

        if (1 <= $interval->d){
            if ($this->minThreshold <= $leftRoom->getCount()) {
                $leftRoom->setCount($leftRoom->getCount() - 1);
            } else {
                $leftRoom->setCount($start);
            }
        }
        $this->dm->persist($leftRoom);
        $this->dm->flush();

        return $leftRoom->getCount();
    }


}