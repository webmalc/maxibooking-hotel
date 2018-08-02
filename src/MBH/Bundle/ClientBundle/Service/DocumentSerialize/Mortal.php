<?php
/**
 * Created by PhpStorm.
 * Date: 28.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;


use MBH\Bundle\PackageBundle\Document\Tourist as TouristBase;
use MBH\Bundle\PackageBundle\Lib\AddressInterface;
use MBH\Bundle\PackageBundle\Lib\DataOfMortalInterface;

/**
 * Class Mortal
 *
 * @property TouristBase $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */
class Mortal extends Common implements DataOfMortalInterface, AddressInterface
{
    use TraitAddress;
    use TraitDataOfMortal;

    protected const METHOD = [
        'getSex',
        'getAge',
    ];

    /**
     * @param $entity
     * @return $this
     */
    public function newInstance($entity)
    {
        $this->instanseOf($entity);
        $this->entity = $entity;
        $this->setAddress($this->entity->getAddressObjectDecomposed());

        return $this;
    }

    /**
     * @return string
     */
    public function getBirthPlaceCity(): string
    {
        $city = '';

        if ($this->entity->getBirthplace() !== null && !empty($id = $this->entity->getBirthplace()->getCity())) {
            $city = $this->billing->getCityById($id)->getName();
        }

        return $city;
    }

    /**
     * @return string
     */
    public function getBirthPlaceCountry(): string
    {
        $country = '';

        if ($this->entity->getBirthplace() !== null && !empty($id = $this->entity->getBirthplace()->getCountryTld())) {
            $country = $this->billing->getCountryByTld($id)->getName();
        }

        return $country;
    }

    protected function getSourceClassName()
    {
        return \MBH\Bundle\PackageBundle\Document\Tourist::class;
    }
}