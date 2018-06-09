<?php

namespace MBH\Bundle\HotelBundle\Document;


use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class FacilityRepository
 */
class FacilityRepository implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getAllByGroupWithoutDescription($isTranslated = false)
    {
        $facilitiesByGroups = [];
        foreach ($this->container->getParameter('mbh.hotel')['facilities'] as $group => $facilities) {
            foreach ($facilities as $facilityId => $facility) {
                if (!isset($facilitiesByGroups[$group])) {
                    $facilitiesByGroups[$group] = [];
                }
                $facilityTitle = $isTranslated
                    ? $this->container->get('translator')->trans($facility['title'])
                    : $facility['title'];
                $facilitiesByGroups[$group][$facilityId] = $facilityTitle;
            }
        }

        return $facilitiesByGroups;
    }

    public function getAll()
    {
        $all = [];
        foreach ($this->getAllByGroupWithoutDescription() as $group => $list) {
            $all = array_merge($all, $list);
        }

        return $all;
    }

    public function sortByConfig($facilities)
    {
        $facilitiesList = array_keys($this->getAll());

        usort($facilities, function ($current, $next) use ($facilitiesList) {
            return array_search($current, $facilitiesList) > array_search($next, $facilitiesList) ? 1 : -1;
        });

        return $facilities;
    }
}