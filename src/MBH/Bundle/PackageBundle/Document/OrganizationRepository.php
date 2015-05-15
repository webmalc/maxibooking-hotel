<?php

namespace MBH\Bundle\PackageBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class OrganizationRepository
 * @package MBH\Bundle\PackageBundle\Document
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class OrganizationRepository extends DocumentRepository
{
    /**
     * @param Hotel $hotel
     * @return Organization/null
     */
    public function getOrganizationByDefaultHotel(Hotel $hotel)
    {
        return $this->findOneBy(['hotels.id' => $hotel->getId()]);
    }

    /**
     * @param Order $order
     * @return Organization|null
     */
    public function getOrganizationByOrder(Order $order)
    {
        /** @var Package[] $packages */
        $packages = $order->getPackages();

        foreach($packages as $package){
            $organization = $this->getOrganizationByDefaultHotel($package->getRoomType()->getHotel());
            if($organization)
                return $organization;
        }
        return null;
    }
}