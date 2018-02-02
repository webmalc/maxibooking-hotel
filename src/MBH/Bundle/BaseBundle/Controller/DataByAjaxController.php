<?php

namespace MBH\Bundle\BaseBundle\Controller;

use MBH\Bundle\BillingBundle\Form\CityType;
use MBH\Bundle\BillingBundle\Form\RegionType;
use MBH\Bundle\BillingBundle\Lib\Model\BillingCheckableInterface;
use MBH\Bundle\BillingBundle\Lib\Model\BillingClientRelatedInterface;
use MBH\Bundle\BillingBundle\Lib\Model\BillingEnablableInterface;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

//TODO: Какие права нужны?
/**
 * @Route("/data")
 * Class DataByAjaxController
 * @package MBH\Bundle\BaseBundle\Controller
 */
class DataByAjaxController extends Controller
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
            if ($form->isSubmitted() && $form->isValid()) {
                $entity = $form->getData();
                if ($entity instanceof BillingClientRelatedInterface) {
                    //TODO: Поменять если останется ID вместо логина
                    $entity->setRequest_client($this->get('kernel')->getClient());
                }
                if ($entity instanceof BillingEnablableInterface && $entity instanceof BillingCheckableInterface) {
                    $entity->setIs_enabled(false);
                    $entity->setIs_checked(false);
                }
                $response = $this->get('mbh.billing.api')->createBillingEntity($endpointSettings, $entity);
                $decodedResponse = json_decode($response->getBody(), true);
                if (!isset($decodedResponse['id'])) {
                    $responseCompiler->setIsSuccessful(false);
                    $this->get('mbh.form_data_handler')->fillFormByBillingErrors($form, $decodedResponse);
                } else {
                    $responseCompiler->setData($decodedResponse);

                    return $responseCompiler->getResponse();
                }
            } else {
                $responseCompiler->setIsSuccessful(false);
            }
        }

        $responseCompiler->setData(['html' => $this->renderView('@MBHBase/DataByAjax/formHtmlForModals.html.twig', ['form' => $form->createView()])]);

        return $responseCompiler->getResponse();
    }
}