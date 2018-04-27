<?php


namespace MBH\Bundle\PriceBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\PriceBundle\Form\Batch\BatchSpecialPromotionType;
use MBH\Bundle\PriceBundle\Form\Batch\BatchSpecialTariffType;
use MBH\Bundle\PriceBundle\Lib\SpecialBatcherException;
use MBH\Bundle\PriceBundle\Services\SpecialBatch\SpecialBatchInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("management/batch/special")
 */
class BatchSpecialController extends BaseController
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/promotion/apply", name="special_batch_promotion_apply" )
     * @Security("is_granted('ROLE_SPECIAL_EDIT')")
     */
    public function promotionApplyAction(Request $request): Response
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $form = $this->createForm(
            BatchSpecialPromotionType::class,
            null,
            [
                'action' => $this->generateUrl('special_batch_promotion_apply'),
                'hotel' => $hotel
            ]
        );
        $batch = $this->get('mbh.batch.promotion');

        return $this->handleRequest($request, $form, $batch);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/tariff/apply", name="special_batch_tariff_apply" )
     * @Security("is_granted('ROLE_SPECIAL_EDIT')")
     */
    public function tariffApplyAction(Request $request): Response
    {
        $hotel = $this->get('mbh.hotel.selector')->getSelected();
        $form = $this->createForm(
            BatchSpecialTariffType::class,
            null,
            [
                'action' => $this->generateUrl('special_batch_tariff_apply'),
                'hotel' => $hotel
            ]
        );
        $batch = $this->get('mbh.batch.tariff');

        return $this->handleRequest($request, $form, $batch);
    }

    private function handleRequest(Request $request, FormInterface $form, SpecialBatchInterface $batch)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $holder = $form->getData();
            $specialHandler = $this->get('mbh.special_handler');
            try {
                $batch->applyBatch($holder);
                $specialHandler->calculatePrices($holder->getSpecialIds());

                return new JsonResponse(['result' => 'ok'], 200);
            } catch (SpecialBatcherException $e) {
                return new JsonResponse(['error' => $e->getMessage()], 500);
            }

        }

        return $this->render(
            '@MBHPrice/Special/batch_promotion_apply.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}