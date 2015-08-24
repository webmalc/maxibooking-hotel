<?php

namespace MBH\Bundle\HotelBundle\Document;


use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class FacilityRepository
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class FacilityRepository implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getAllByGroup()
    {
        return $this->container->getParameter('mbh.hotel')['facilities'];
    }

    public function sortByConfig($facilities)
    {
        $facilitiesList = [];
        foreach($this->getAllByGroup() as $group => $list) {
            $facilitiesList = array_merge($facilitiesList, array_keys($list));
        }

        usort($facilities, function($current, $next) use ($facilitiesList) {
            return array_search($current, $facilitiesList) > array_search($next, $facilitiesList) ? 1 : -1;
        });
        return $facilities;
    }
}