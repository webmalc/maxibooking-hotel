<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BillingBundle\Lib\Model\ClientService;
use MBH\Bundle\BillingBundle\Lib\Model\PaymentOrder;
use MBH\Bundle\BillingBundle\Lib\Model\PaymentSystem;
use MBH\Bundle\BillingBundle\Service\BillingResponseHandler;
use MBH\Bundle\UserBundle\Form\ClientContactsType;
use MBH\Bundle\UserBundle\Form\ClientTariffType;
use MBH\Bundle\UserBundle\Form\PayerType;
use MBH\Bundle\UserBundle\Form\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * User profile controller.
 * @Route("user")
 */
class ProfileController extends Controller
{
    /**
     * Profile form
     *
     * @Route("/profile", name="user_profile")
     * @Security("is_granted('ROLE_PROFILE') || is_granted('ROLE_ACCESS_WITH_TOKEN')")
     * @Method("GET")
     * @Template()
     */
    public function profileAction()
    {
        if (!$this->isGranted('ROLE_PROFILE')) {
            return $this->redirectToRoute('user_payment');
        }

        $form = $this->createForm(ProfileType::class, $this->getUser());

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Profile form
     *
     * @Route("/profile", name="user_profile_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_PROFILE')")
     * @Template("MBHUserBundle:Profile:profile.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function profileUpdateAction(Request $request)
    {
        $entity = $this->getUser();
        $form = $this->createForm(ProfileType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->container->get('fos_user.user_manager')->updateUser($entity);
            if ($entity->getLocale()) {
                $this->get('session')->set('_locale', $entity->getLocale());
            }
            $this->addFlash('success', 'controller.profileController.profile_saved_success');

            return $this->redirectToRoute('user_profile');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Template()
     * @Route("/contacts", name="user_contacts")
     * @Security("is_granted('ROLE_PAYMENTS')")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function contactsAction(Request $request)
    {
        $client = $this->get('mbh.client_manager')->getClient();
        $form = $this->createForm(ClientContactsType::class, $client);

        $clientEmail = $client->getEmail();
        $clientName = $client->getName();
        $clientPhone = $client->getPhone();

        $form->handleRequest($request);

        if ($clientEmail !== $client->getEmail()) {
            throw new Exception('Changed e-mail field value which can not be changed');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $clientUpdateResult = $this->get('mbh.client_manager')->updateClient($client);
            if ($clientUpdateResult->isSuccessful()) {
                if ($client->getPhone() !== $clientPhone) {
                    $this->addFlash('success',
                        $this->get('translator')->trans('controller.profileController.phone_successful_updated', [
                            '%newPhoneNumber%' => $client->getPhone()
                        ]));
                }
                if ($clientName !== $client->getName()) {
                    $this->addFlash('success', 'controller.profileController.client_successful_updated');
                }
            } else {
                if (!empty($clientUpdateResult->getErrors())) {
                    foreach ($clientUpdateResult->getErrors() as $fieldName => $errorMessages) {
                        foreach ($errorMessages as $errorMessage) {
                            if ($form->has($fieldName)) {
                                $form->get($fieldName)->addError(new FormError($errorMessage));
                            } else {
                                $form->addError(new FormError($errorMessage));
                            }
                        }
                    }
                } else {
                    $this->addBillingErrorFlash();
                }
            }

            $saveCloseString = $request->request->get('save_close');
            if (isset($saveCloseString)) {
                return $this->redirectToRoute('package');
            }
        }

        return [
            'form' => $form->createView(),
            'client' => $client
        ];
    }

    /**
     * @Template()
     * @Security("is_granted('ROLE_PAYMENTS')")
     * @Route("/tariff", name="user_tariff", options={"expose"=true})
     * @return array
     */
    public function tariffAction()
    {
        return [
            'tariffsData' => $this->get('mbh.billing.api')->getTariffsData(),
        ];
    }

    /**
     * @Route("/update_tariff_modal", name="update_tariff_modal", options={"expose"=true})
     * @Template
     * @param Request $request
     * @return array|Response
     */
    public function updateTariffModalAction(Request $request)
    {
        $form = $this->createForm(ClientTariffType::class);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $result = $this->get('mbh.billing.api')->changeTariff($form->getData());
                if ($result->isSuccessful()) {
                    $this->addFlash('success', 'view.personal_account.tariff.change_tariff.success');

                    return new Response('', 302);
                } else {
                    $errors = [BillingResponseHandler::NON_FIELD_ERRORS => [$this->get('mbh.billing_response_handler')->getUnexpectedErrorText()]];
                    $this->get('mbh.form_data_handler')->fillFormByBillingErrors($form, $errors);
                }
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Template()
     * @Security("is_granted('ROLE_PAYMENTS')")
     * @Route("/payer", name="user_payer")
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function payerAction(Request $request)
    {
        $form = $this->createForm(PayerType::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $errors = $this->get('mbh.client_payer_manager')->saveClientPayerAndReturnErrors($form->getData());
                if (!empty($errors)) {
                    $this->addFlash('error', 'controller.profileController.payer_failed_saved');
                    $this->get('mbh.form_data_handler')->fillFormByBillingErrors($form, $errors);
                } else {
                    $this->addFlash('success', 'controller.profileController.payer_successfull_saved');
                }
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Template()
     * @Security("is_granted('ROLE_PAYMENTS')")
     * @Route("/payment", name="user_payment")
     * @return array
     */
    public function paymentAction()
    {
        $beginDate = new \DateTime('midnight - 1 month');
        $endDate = new \DateTime('midnight');

        return [
            'beginDate' => $beginDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * @Route("/payments_list_json", name="payments_list_json", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function paymentsListAction(Request $request)
    {
        $formData = $request->request->get('form');
        $beginDate = isset($formData['begin']) && !empty($formData['begin'])
            ? $this->helper->getDateFromString($formData['begin'])
            : new \DateTime('midnight - 300 days');

        $endDate = isset($formData['end']) && !empty($formData['end'])
            ? $this->helper->getDateFromString($formData['end'])
            : new \DateTime('midnight');

        $paidStatus = isset($formData['paidStatus']) ? $formData['paidStatus'] : 'all';

        $client = $this->get('mbh.client_manager')->getClient();

        $requestResult = $this->get('mbh.billing.api')->getClientOrdersResultByCreationDate($client, $beginDate, $endDate);
        if (!$requestResult->isSuccessful()) {
            $orders = [];
            $this->addBillingErrorFlash();
        } else {
            $orders = array_filter($requestResult->getData(), function (PaymentOrder $order) use ($beginDate, $endDate, $paidStatus) {
                return $paidStatus === 'all'
                    || ($order->getStatus() === 'paid' && $paidStatus  === 'paid')
                    || ($order->getStatus() !== 'paid' && $paidStatus  === 'not-paid');
            });
        }

        return $this->render('@MBHUser/Profile/paymentsList.json.twig', [
            'orders' => $orders,
            'draw' => $request->get('draw'),
        ]);
    }

    /**
     * @Template()
     * @Security("is_granted('ROLE_PAYMENTS')")
     * @Route("/payment_order/{orderId}", name="show_payment_order")
     * @param $orderId
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function showPaymentOrderAction($orderId)
    {
        $client = $this->get('mbh.client_manager')->getClient();
        $billingApi = $this->get('mbh.billing.api');
        $order = $billingApi->getClientOrderById($orderId);
        if ($client->getLogin() !== $order->getClient()) {
            $this->addBillingErrorFlash();

            return $this->redirectToRoute('user_payment');
        }

        $clientServices = [];
        foreach ($order->getClient_services() as $serviceUrl) {
            $clientServices[] = $billingApi->getBillingEntityByUrl($serviceUrl, ClientService::class);
        }

        return [
            'order' => $order,
            'services' => $clientServices
        ];
    }

    /**
     * @Security("is_granted('ROLE_PAYMENTS')")
     * @Route("/payment_order/{orderId}/payment_systems", name="order_payment_systems", options={"expose"=true})
     * @param $orderId
     * @return JsonResponse
     */
    public function payOrderModalAction($orderId)
    {
//        $errors = $this->get('mbh.client_payer_manager')->getErrorsCausedByUnfilledDataForPayment();
//        if (!empty($errors)) {
//            return ['errors' => $errors];
//        }

        $billingApi = $this->get('mbh.billing.api');
        $order = $billingApi->getClientOrderById($orderId);
        $paymentSystemsResult = $billingApi->getPaymentSystemsForOrder($order);

        $paymentSystems = [];
        /** @var PaymentSystem $paymentSystem */
        foreach ($paymentSystemsResult->getData() as $paymentSystem) {
            $paymentSystemData = ['id' => $paymentSystem->getId(), 'name' => $paymentSystem->getName()];
            if ($paymentSystem->getId() === 'bill') {
                $paymentSystemData['html'] = $paymentSystem->getHtml();
            }
            $paymentSystems[] = $paymentSystemData;
        }

        return new JsonResponse([
            'paymentTypes' => $paymentSystems,
            'order' => [
                'price' => $order->getPrice(),
                'currency' => $order->getPrice_currency(),
                'id' => $order->getId()
            ]
        ]);
    }

    /**
     * @Template()
     * @Route("/payment_system_details/{paymentSystemName}/{orderId}", name="payment_system_details", options={"expose"=true})
     * @param string $paymentSystemName
     * @param int $orderId
     * @return array
     */
    public function paymentSystemDetailsAction(string $paymentSystemName, int $orderId)
    {
        /** @var PaymentSystem $paymentSystem */
        $paymentSystem = $this
            ->get('mbh.billing.api')
            ->getPaymentSystemForOrderByName($paymentSystemName, $orderId);

        return ['html' => $paymentSystem->getHtml()];
    }

    /**
     * @Route("/client_successful_payment", name="client_successful_payment", options={"expose"=true})
     * @Template("@MBHUser/Profile/paymentResultPage.html.twig")
     * @return array
     */
    public function paymentSuccessfulPageAction()
    {
        return ['success' => true];
    }

    private function addBillingErrorFlash()
    {
        $this->addFlash('error',
            $this->get('translator')->trans('controller.profileController.request_error', [
                '%supportEmail%' => $this->getParameter('support')['email']
            ]));
    }
}
