<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Facility;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/facilities")
 * Class FacilityController
 * @package MBH\Bundle\HotelBundle\Controller
 */
class FacilityController extends BaseController
{
    /**
     * @Security("is_granted('ROLE_UPDATE_FACILITIES')")
     * @Route("/list", name="facilities_list")
     * @Template()
     */
    public function listAction()
    {
        $facilityDocs = $this->dm
            ->getRepository('MBHHotelBundle:Facility')
            ->findBy(['hotel.id' => $this->hotel->getId()]);

        return [
            'facilities' => $this->getParameter('mbh.hotel')['facilities'],
            'facilityDocs' => $this->helper->sortByValue($facilityDocs, false, 'getFacilityId')
        ];
    }

    /**
     * @Security("is_granted('ROLE_UPDATE_FACILITIES')")
     * @Route("/save_list", name="save_list", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAction(Request $request)
    {
        $defaultFacilities = $this->getParameter('mbh.hotel')['facilities'];
        $facilitiesData = $request->get('facilities');
        foreach ($facilitiesData as $group => $facilitiesByGroup) {
            $requestDefaultFacilities = isset($facilitiesByGroup['no-doc']) ? $facilitiesByGroup['no-doc'] : [];
            foreach ($requestDefaultFacilities as $facilityId => $description) {
                if ($defaultFacilities[$group][$facilityId]['description'] !== $description) {
                    $newFacilityDoc = (new Facility())
                        ->setFacilityId($facilityId)
                        ->setDescription($description)
                        ->setHotel($this->hotel);
                    $this->dm->persist($newFacilityDoc);
                }
            }

            $facilityDocs = $this->dm
                ->getRepository('MBHHotelBundle:Facility')
                ->findBy(['hotel.id' => $this->hotel->getId()]);
            $facilityDocsByIds = $this->helper->sortByValue($facilityDocs, false, 'getFacilityId');
            $requestFacilitiesDocs = isset($facilitiesByGroup['with-doc']) ? $facilitiesByGroup['with-doc'] : [];
            foreach ($requestFacilitiesDocs as $facilityId => $description) {
                /** @var Facility $facilityDoc */
                $facilityDoc = $facilityDocsByIds[$facilityId];
                if ($facilityDoc->getDescription() !== $description) {
                    $facilityDoc->setDescription($description);
                }
            }
        }

        $this->dm->flush();
        $this->addFlash('success', 'Все ок!');

        return $this->redirectToRoute('facilities_list');
    }
}