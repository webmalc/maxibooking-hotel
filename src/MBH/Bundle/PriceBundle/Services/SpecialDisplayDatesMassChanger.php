<?php


namespace MBH\Bundle\PriceBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Lib\SpecialFilter;

class SpecialDisplayDatesMassChanger
{

    const DEFAULT_PERIOD = 7;
    /** @var  DocumentManager */
    private $dm;

    /**
     * SpecialDisplayDatesMassChanger constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function changeDates(
        int $period = null,
        \DateTime $begin = null,
        \DateTime $end = null,
        RoomType $roomType = null,
        callable $output = null
    ): void {
        $filter = new SpecialFilter();
        if ($begin) {
            $filter->setBegin($begin);
        }
        if ($end) {
            $filter->setEnd($end);
        }
        if ($roomType) {
            $filter->setRoomType($roomType);
        }
        $specials = $this->dm->getRepository('MBHPriceBundle:Special')->getFiltered($filter);
        if (!$period) {
            $period = self::DEFAULT_PERIOD;
        }
        if (is_iterable($specials)) {
            foreach ($specials as $special) {
                if ($output){
                    $message = 'Handle special '.$special->getId();
                    $output($message);
                }
                /** @var Special $special */
                $displayFrom = (clone $special->getBegin())->modify('- '.$period.' days');
                $displayTo = (clone $special->getBegin())->modify('+ '.$period.' days');
                $special
                    ->setDisplayFrom($displayFrom)
                    ->setDisplayTo($displayTo);
            }
            $this->dm->flush();
        }
    }


}