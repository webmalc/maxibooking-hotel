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

    public function getAllGrouped($isTranslated = false, $byGroups = true, $field = 'title')
    {
        $result = [];
        foreach ($this->container->getParameter('mbh.hotel')['facilities'] as $group => $facilities) {
            foreach ($facilities as $facilityId => $facility) {
                if ($byGroups && !isset($result[$group])) {
                    $result[$group] = [];
                }

                $fieldData = $isTranslated
                    ? $this->container->get('translator')->trans($facility[$field])
                    : $facility[$field];

                if ($byGroups) {
                    $result[$group][$facilityId] = $fieldData;
                } else {
                    $result[$facilityId] = $fieldData;
                }
            }
        }

        return $result;
    }

    public function getAll()
    {
        $all = [];
        foreach ($this->getAllGrouped() as $group => $list) {
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

    /**
     * @param Hotel $hotel
     * @param string $locale
     * @param array $facilitiesIds
     * @return array
     */
    public function getActualFacilitiesData(string $locale, Hotel $hotel = null, array $facilitiesIds = null)
    {
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');
        $translator = $this->container->get('translator');

        $descriptionsByIds = $this->getAllGrouped(true, false, 'description');
        $titlesByIds = $this->getAllGrouped(false, false, 'title');

        $facilityDocsByIds = is_null($hotel) ? [] : $this->getFacilityDocsByIds($hotel);

        $facilityDescriptionTranslationDocs = $dm
            ->getRepository('GedmoTranslatable:Translation')
            ->findBy(['locale' => $locale, 'objectClass' => Facility::class, 'field' => 'description']);
        $transDocsByIds = $this->container
            ->get('mbh.helper')
            ->sortByValue($facilityDescriptionTranslationDocs, false, 'getForeignKey');

        if (is_null($facilitiesIds)) {
            $facilitiesIds = array_keys($descriptionsByIds);
        }

        $result = [];
        foreach ($facilitiesIds as $facilityId) {
            $description = isset($facilityDocsByIds[$facilityId]) && isset($transDocsByIds[$facilityDocsByIds[$facilityId]->getId()])
                ? $transDocsByIds[$facilityDocsByIds[$facilityId]->getId()]->getContent()
                : $translator->trans($descriptionsByIds[$facilityId], [], null, $locale);

            $result[$facilityId] = [
                'title' => $translator->trans($titlesByIds[$facilityId], [], null, $locale),
                'description' => $description
            ];
        }

        return $result;
    }

    /**
     * @param Hotel $hotel
     * @return array
     */
    public function getFacilityDocsByIds(Hotel $hotel)
    {
        $facilityDocs = $this->container->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHHotelBundle:Facility')
            ->findBy(['hotel.id' => $hotel->getId()]);

        return $this->container
            ->get('mbh.helper')
            ->sortByValue($facilityDocs, false, 'getFacilityId');
    }
}