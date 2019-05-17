<?php

namespace MBH\Bundle\BillingBundle\Controller;

use MBH\Bundle\BillingBundle\Form\CityType;
use MBH\Bundle\BillingBundle\Form\RegionType;
use MBH\Bundle\BillingBundle\Lib\Model\City;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

//TODO: Какие права нужны?

/**
 * @Route("/billing_data")
 * Class DataByAjaxController
 * @package MBH\Bundle\BaseBundle\Controller
 */
class BillingDataController extends Controller
{
    /**
     * @Route("/create_region", name="create_region", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function newRegionAction(Request $request)
    {
        return $this->handleBillingEntityAction($request, RegionType::class, BillingApi::REGIONS_ENDPOINT_SETTINGS);
    }

    /**
     * @Route("/create_city", name="create_city", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function newCityAction(Request $request)
    {
        return $this->handleBillingEntityAction($request, CityType::class, BillingApi::CITIES_ENDPOINT_SETTINGS);
    }

    /**
     * @param Request $request
     * @param string $formType
     * @param array $endpointSettings
     * @return JsonResponse
     */
    private function handleBillingEntityAction(Request $request, string $formType, array $endpointSettings)
    {
        $form = $this->createForm($formType, new $endpointSettings['model']);
        $responseCompiler = $this->get('mbh.api_response_compiler');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $responseCompiler = $this->get('mbh.billing_data_handler')->handleNewEntityForm($form, $responseCompiler, $endpointSettings);
        }

        if ($request->isMethod('GET') || !$responseCompiler->isSuccessful()) {
            $responseCompiler->setData(['html' => $this->renderView('@MBHBase/formHtmlForModals.html.twig', ['form' => $form->createView()])]);
        }

        return $responseCompiler->getResponse();
    }
}
