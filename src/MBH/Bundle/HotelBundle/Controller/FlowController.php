<?php

namespace MBH\Bundle\HotelBundle\Controller;

use Gedmo\Mapping\Annotation\Translatable;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\HotelFlow\HotelFlow;
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

        /** @var HotelFlow $flow */
        $flow = $this->get('mbh.hotel_flow')->init($hotel);

        $formData = in_array($flow->getCurrentStepNumber(), [8]) ? null : $this->hotel;
        $form = $flow->createForm($formData);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $multiLangFields = $this->get('mbh.document_fields_manager')
                ->getPropertiesByAnnotationClass(Hotel::class, Translatable::class);
            $this->get('mbh.form_data_handler')
                ->saveTranslationsFromMultipleFieldsForm($form, $request, $multiLangFields);
            $this->dm->persist($hotel);

            if ($flow->getCurrentStepNumber() === 7) {
                $this->dm->persist($hotel->getDefaultImage());
            }

            if ($flow->getCurrentStepNumber() === 8) {
                $savedImage = $form->getData();
                $hotel->addImage($savedImage);
                $this->dm->persist($savedImage);
            }

            $this->dm->flush();

            if ($flow->isButtonClicked('next') || $flow->isButtonClicked('back')) {
                $flow->nextStep();
            }

            $formData = in_array($flow->getCurrentStepNumber(), [8]) ? null : $this->hotel;
            $form = $flow->createForm($formData);
        }

        return [
            'form' => $form->createView(),
            'flow' => $flow,
            'hotel' => $hotel
        ];
    }
}