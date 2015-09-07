<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\ClientBundle\Service\Mbhs;

/**
 * Class UnwelcomeRepository
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class UnwelcomeRepository
{
    /**
     * @var Mbhs
     */
    protected $mbhs;

    public function __construct(Mbhs $mbhs)
    {
        $this->mbhs = $mbhs;
    }

    /**
     * @param UnwelcomeItem $blackListInfo
     * @return bool
     */
    public function add(UnwelcomeItem $blackListInfo)
    {
        return $this->mbhs->addUnwelcomeItem($blackListInfo);
    }

    /**
     * @param UnwelcomeItem $blackListInfo
     * @return bool
     */
    public function update(UnwelcomeItem $blackListInfo)
    {
        return $this->mbhs->updateUnwelcomeItem($blackListInfo);
    }

    /**
     * @param Tourist $tourist
     * @return UnwelcomeItem|null
     */
    public function findOneByTourist(Tourist $tourist)
    {
        $data = $this->mbhs->findUnwelcomeItemByTourist($tourist);
        if($data && $data['blackListInfo']) {
            $blackListInfo = new UnwelcomeItem();

            $blackListInfo->setTourist($tourist);
            $blackListInfo->setIsAggressor($data['blackListInfo']['isAggressor']);
            $blackListInfo->setComment($data['blackListInfo']['comment']);

            return $blackListInfo;
        }

        return null;
    }

    /**
     * @param Tourist $tourist
     * @return UnwelcomeItem|null
     */
    public function deleteByTourist(Tourist $tourist)
    {
        return $this->mbhs->deleteUnwelcomeItemByTourist($tourist);
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