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
     * @param Request $request
     * @return array
     */
    public function listAction(Request $request)
    {
        $locale = $request->query->get('locale') ?? $this->getUser()->getLocale() ?? $this->getParameter('locale');

        return [
            'facilitiesData' => $this->get('mbh.facility_repository')->getActualFacilitiesData($locale, $this->hotel),
            'facilitiesLocale' => $locale
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
        $requestFacilitiesData = $request->get('facilities');
        $facilitiesLocale = $request->get('facilitiesLocale');

        $facilityRepo = $this->get('mbh.facility_repository');
        $facilityDocsByIds = $facilityRepo->getFacilityDocsByIds($this->hotel);
        $facilitiesData = $facilityRepo->getActualFacilitiesData($facilitiesLocale, $this->hotel);

        foreach ($requestFacilitiesData as $facilityId => $description) {
            if ($facilitiesData[$facilityId]['description'] !== $description) {
                if (isset($facilityDocsByIds[$facilityId])) {
                    $facilityDoc = $facilityDocsByIds[$facilityId];
                } else {
                    $facilityDoc = (new Facility())
                        ->setFacilityId($facilityId)
                        ->setHotel($this->hotel);
                    $this->dm->persist($facilityDoc);
                }

                $facilityDoc->setLocale($facilitiesLocale)->setDescription($description);
            }
        }

        $this->dm->flush();
        $this->addFlash('success', 'facility_controller.facility_description.save_table.success');

        return $this->redirectToRoute('facilities_list', ['locale' => $facilitiesLocale]);
    }
}