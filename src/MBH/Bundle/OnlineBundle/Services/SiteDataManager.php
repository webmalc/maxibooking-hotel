<?php

namespace MBH\Bundle\OnlineBundle\Services;


use MBH\Bundle\HotelBundle\Document\Hotel;

class SiteDataManager
{
    public function checkMandatoryDataForSite(Hotel $hotel)
    {
        $notFilledData = [];
        if (empty($hotel->getDescription())) {
            $notFilledData[] = ['message' => 'Не заполнено описание'];
        }
    }
}