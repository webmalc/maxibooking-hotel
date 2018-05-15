<?php
/**
 * Created by PhpStorm.
 * Date: 27.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;

use MBH\Bundle\HotelBundle\Document\Hotel as HotelBase;

/**
 * Class Hotel
 *
 * @property HotelBase $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */

class Hotel extends Common
{
    use TraitAddress;


    public function getPhoneNumber(): string
    {
        return $this->entity->getContactInformation() !== null ? $this->entity->getContactInformation()->getPhoneNumber() : '';
    }

    public function getFullTitle(): string
    {
        return $this->entity->getFullTitle() ?? '';
    }

    public function getInternationalTitle(): string
    {
        return $this->entity->getInternationalTitle() ?? '';
    }

    public function getInternationalStreetName(): string
    {
        return $this->getInternationalStreetName() ?? '';
    }

    public function getLogo(): string
    {
        $return = '';
        if (!empty($this->entity->getLogo())){
            $return = '<img src="{{ absolute_url(asset(vich_uploader_asset(hotel.logoImage, \'imageFile\')|imagine_filter(\'thumb_95x80\'))) }}" alt="Hotel logo" />';
        }

        return $return;
    }

}