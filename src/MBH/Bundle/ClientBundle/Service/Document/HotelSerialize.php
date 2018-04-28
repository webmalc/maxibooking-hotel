<?php
/**
 * Created by PhpStorm.
 * Date: 27.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\HotelBundle\Document\Hotel;


class HotelSerialize extends CommonSerialize
{
    use AddressSerialize;

    /**
     * @var Hotel
     */
    protected $entity;

    public function __construct(Hotel $hotel)
    {
        $this->entity = $hotel;
        $this->setAddress($this->entity);
    }

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

    public function getStreet(): string
    {
        return $this->entity->getStreet()?? '';
    }

    public function getInternationalStreetName(): string
    {
        return $this->getInternationalStreetName() ?? '';
    }



}