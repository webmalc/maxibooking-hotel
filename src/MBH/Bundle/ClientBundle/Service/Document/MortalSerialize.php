<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\PackageBundle\Document\Tourist;


class MortalSerialize extends CommonSerialize
{
    use AddressSerialize;

    /**
     * @var Tourist
     */
    protected $entity;


    public function __construct(Tourist $mortal)
    {
        $this->entity = $mortal;
        $this->setAddress($this->entity->getAddressObjectDecomposed());
    }

    public function getSex(): string
    {
        return $this->entity->getSex() ?? '';
    }

    public function getFullName(): string
    {
        return $this->entity->getFullName() ?? '';
    }

    public function getLastName(): string
    {
        return $this->entity->getLastName() ?? '';
    }

    public function getFirstName(): string
    {
        return $this->entity->getFirstName() ?? '';
    }

    public function getBirthday(): string
    {
        return $this->entity->getBirthday() !== null ? $this->entity->getBirthday()->format('d.m.Y') : '';
    }

    public function getAge(): string
    {
        return $this->entity->getAge() !== null ? $this->entity->getAge() : '';
    }

    public function getEmail(): string
    {
        return $this->entity->getEmail() ?? '';
    }

    public function getBirthPlaceCity(): string
    {
        $city = '';

        if ($this->entity->getBirthplace() !== null && !empty($id = $this->entity->getBirthplace()->getCity())){
            $city = $this->billing->getCityById($id)->getName();
        }

        return $city;
    }

    public function getBirthPlaceCountry(): string
    {
        $country = '';

        if ($this->entity->getBirthplace() !== null && !empty($id = $this->entity->getBirthplace()->getCountryTld())){
            $country = $this->billing->getCountryByTld($id)->getName();
        }
        return $country;
    }
}