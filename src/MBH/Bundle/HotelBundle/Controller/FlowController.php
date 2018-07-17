<?php

namespace MBH\Bundle\HotelBundle\Controller;

use Gedmo\Mapping\Annotation\Translatable;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/flow")
 * Class FlowController
 * @package MBH\Bundle\HotelBundle\Controller
 */
class FlowController extends BaseController
{
    /**
     * @Template()
     * @Route("/hotel", name="hotel_flow")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function hotelFlowAction(Request $request)
    {
        //TODO: Пока что для текущего отеля
        $hotel = $this->hotel;
        $flow = $this->get('mbh.hotel_flow')->init($hotel);
        $form = $flow->createForm($hotel);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $multiLangFields = $this->get('mbh.document_fields_manager')
                ->getPropertiesByAnnotationClass(Hotel::class, Translatable::class);
            $this->get('mbh.form_data_handler')
                ->saveTranslationsFromMultipleFieldsForm($form, $request, $multiLangFields);
            $this->dm->persist($hotel);

            if ($flow->getCurrentStepNumber() === 7) {
                /** @var Image $savedImage */
                $savedImage = $hotel->getImages()->last();
                $savedImage->setIsDefault(true);
                $this->dm->persist($savedImage);
            }

            if ($flow->getCurrentStepNumber() === 8) {
                $this->dm->persist($hotel->getImages()->last());
            }

            $this->dm->flush();

            if (!$request->request->has('add_image')) {
                $flow->nextStep();

                if (!$flow->isLastStep()) {
//                    $flow->reset();
//
//                    return $this->redirectToRoute('hotel_flow');
                }
            }

            $form = $flow->createForm($this->hotel);
        }

        return [
            'form' => $form->createView(),
            'flow' => $flow,
            'hotel' => $hotel
        ];
    }
}