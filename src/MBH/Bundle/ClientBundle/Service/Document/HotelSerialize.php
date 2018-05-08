<?php
/**
 * Created by PhpStorm.
 * Date: 27.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HotelSerialize
 *
 * @property Hotel $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\Document
 */

class HotelSerialize extends CommonSerialize
{
    use AddressSerialize;


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