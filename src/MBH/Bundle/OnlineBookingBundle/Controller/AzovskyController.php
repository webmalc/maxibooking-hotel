<?php


namespace MBH\Bundle\OnlineBookingBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ClientBundle\Service\PaymentSystem\NewRbkInvoiceCreate;
use MBH\Bundle\OnlineBookingBundle\Form\ReservationType;
use MBH\Bundle\OnlineBookingBundle\Form\SignType;
use MBH\Bundle\PriceBundle\Lib\PaymentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AzovskyController
 * @Route("/azovsky")
 */
class AzovskyController extends Controller
{

    private const ONLINE = 'online';

    private const RESERVE = 'reserve';

    /**
     * @return Response
     * @Route("/results", name="az_results")
     */
    public function azovskyResultsAction(): Response
    {
        return $this->render('@MBHOnlineBooking/Azovsky/results.js.twig');
    }

    /**
     * @param Request $request
     * @Route("/create-order", name="az_create_order", options={"expose" = true})
     * @return JsonResponse
     * @throws \Exception
     */
    public function createOrderAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(SignType::class);
        $form->submit($data);
        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = [
                    'path' => $error->getCause()->getPropertyPath(),
                    'message' => $error->getCause()->getMessage(),
                ];
            }

            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'form is invalid',
                    'errors' => $errors,
                ]
            );
        }

        $formData = $form->getData();
        $data = DefaultController::prepareOnlineData($formData);
        $paymentType = PaymentType::PAYMENT_TYPE_LIST[$formData['paymentType']];
        //OnlinePayment  - оплаченная цена (формируется взависимости от выбора. Искать в форме)
        $onlinePaymentSum = (int)$formData['total'] / 100 * $paymentType['value'];
        $cash = ['total' => $onlinePaymentSum];
        $data['onlinePaymentType'] = 'online_full';
        $orderManager = $this->container->get('mbh.order_manager');
        try {
            $order = $orderManager->createPackages($data);
        } catch (Exception $e) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Exception in order manager',
                ]

            );
        }
        //** Пишу, а кровь из глаз льет ручем. */
        $invoice = new NewRbkInvoiceCreate($this->container);
        $total = $order->getPackages()[0]->getPrice();
        $packageId = $order->getPackages()[0]->getId();

        $invoiceData = $invoice->getDataFromInvoceInside($total, $packageId)->arrayData();


        $result = [
            'status' => 'success',
            'orderId' => $order->getId(),
            'total' => $total,
            'invoice' => $invoiceData,
            'type' => self::ONLINE,

        ];

        $notificator = $this->container->get('mbh.online_booking_notificator');

        try {
            $notificator->newOrderNotify($order);
        } catch (Exception $e) {
            $logger = $this->container->get('mbh.online.booking_logger');
            $logger->error('Error when try to send notification with order. '.$e->getMessage(), [$order]);
        }

        return $this->json($result);
    }

    /**
     * @param Request $request
     * @Route("/create-reservation", name="az_create_reservation", options={"expose" = true})
     * @return JsonResponse
     */
    public function createReservationAction(Request $request)
    {

        $rawData = json_decode($request->getContent(), true);
        foreach (['paymentType', 'patronymic', 'offerta'] as $uselessField) {
            if (isset($rawData[$uselessField])) {
                unset($rawData[$uselessField]);
            }
        }

        $errorResult = [
            'status' => 'error',
            'type' => self::RESERVE,

        ];

        $form = $this->createForm(ReservationType::class);
        $form->submit($rawData);
        if (!$form->isValid()) {
            return $this->json($errorResult);
        }

        $formData = $form->getData();
        $data = DefaultController::prepareOnlineData($formData);
        $data['total'] = $formData['total'] ?? 'error';

        $notificator = $this->container->get('mbh.online_booking_notificator');
        try {
            $notificator->reservationNotify($data);
        } catch (\Exception $e) {
            $logger = $this->container->get('mbh.online.booking_logger');
            $logger->error('Error when try to send notification when reserve. '.$e->getMessage(), [$data]);
            return $this->json($errorResult);
        }

        $result = [
            'status' => 'success',
            'type' => self::RESERVE,
        ];


        return $this->json($result);
    }

    /**
     * @param Request $request
     * @Route("/log-errors", name="az_error_log", options={"expose" = true})
     */
    public function errorLogAction(Request $request)
    {

    }
}