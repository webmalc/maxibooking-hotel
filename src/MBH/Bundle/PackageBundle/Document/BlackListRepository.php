<?php

namespace MBH\Bundle\PackageBundle\Document;

/**
 * Class BlackListRepository
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class BlackListRepository
{
    public function add(BlackListInfo $blackListInfo)
    {


        return true;
    }

    /**
     * @param Tourist $tourist
     * @return BlackListInfo|null
     */
    public function findOneByTourist(Tourist $tourist)
    {
        $blackListInfo = new BlackListInfo();

        $blackListInfo->setTourist($tourist);
        //$blackListInfo->setHotel();
        //$blackListInfo->setComment('')

        return null;
        //return $blackListInfo;
    }

    /**
     * @param Tourist[] $tourists
     */
    public function updateTourists(array $tourists)
    {
        foreach($tourists as $tourist) {
            //$tourist->setIsInBlackList();
        }
    }
}