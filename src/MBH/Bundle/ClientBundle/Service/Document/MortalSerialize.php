<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\Document;


use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\DataOfMortalInterface;

/**
 * Class MortalSerialize
 *
 * @property Tourist $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\Document
 */

class MortalSerialize extends CommonSerialize implements DataOfMortalInterface
{
    use AddressSerialize;
    use DataOfMortalSerialize;

    public function newInstance($entity)
    {
        $this->entity = $entity;
        $this->setAddress($this->entity->getAddressObjectDecomposed());
        return $this;
    }

    public function getSex(): string
    {
        return $this->entity->getSex() ?? '';
    }

    public function getAge(): string
    {
        return $this->entity->getAge() !== null ? $this->entity->getAge() : '';
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