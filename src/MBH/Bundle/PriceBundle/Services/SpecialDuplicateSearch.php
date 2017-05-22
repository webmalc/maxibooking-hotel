<?php


namespace MBH\Bundle\PriceBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\PriceBundle\Document\Special;

class SpecialDuplicateSearch
{
    /** @var  DocumentManager */
    private $dm;

    /**
     * SpecialDuplicateSearch constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function search()
    {
        $result = [];
        $specials = $this->dm->getRepository('MBHPriceBundle:Special')->findAll();
        foreach ($specials as $special) {
            /** @var Special $special */
            $begin = $special->getBegin()->format('d.m.Y');
            $end = $special->getEnd()->format('d.m.Y');
            $virtualRoom = $special->getVirtualRoom();
            if ($virtualRoom && $virtualRoom instanceof Room) {
                $virtualRoomId = $virtualRoom->getId();
                $result[$begin.$end.$virtualRoomId][] = $special;
            }

        }

        $duplicates = array_filter(
            $result,
            function ($special) {
                return 2 <= count($special);
            }
        );

        return $duplicates;

    }


}