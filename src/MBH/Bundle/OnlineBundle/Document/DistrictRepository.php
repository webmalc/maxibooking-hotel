<?php

namespace MBH\Bundle\OnlineBundle\Document;

/**
 * Class DistrictRepository
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class DistrictRepository extends AbstractFileRepository
{
    public function getFilePath()
    {
        return $this->fileLocator->locate('@MBHOnlineBundle/Resources/fixture/district.txt');
    }
}