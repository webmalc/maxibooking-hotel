<?php

namespace MBH\Bundle\OnlineBundle\Document;


/**
 * Class HighwayRepository
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class HighwayRepository extends AbstractFileRepository
{
    public function getFilePath()
    {
        return $this->fileLocator->locate('@MBHOnlineBundle/Resources/fixture/highway.txt');
    }
}