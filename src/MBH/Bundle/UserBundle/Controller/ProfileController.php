<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BillingBundle\Lib\Model\ClientService;
use MBH\Bundle\BillingBundle\Lib\Model\PaymentOrder;
use MBH\Bundle\UserBundle\Form\ClientContactsType;
use MBH\Bundle\UserBundle\Form\ClientTariffType;
use MBH\Bundle\UserBundle\Form\PayerType;
use MBH\Bundle\UserBundle\Form\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

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
     * @Method("GET")
     * @Security("is_granted('ROLE_PASSWORD')")
     * @Template()
     */
    public function profileAction()
    {
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
     * @Security("is_granted('ROLE_PASSWORD')")
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

            $this->getRequest()->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.profileController.new_password_saved_success'));
            return $this->redirect($this->generateUrl('user_profile'));
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
     * @throws Exception
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
     * @Route("/tariff", name="user_tariff")
     * @param Request $request
     * @return array
     */
    public function tariffAction(Request $request)
    {
        $billingApi = $this->get('mbh.billing.api');

        $form = $this->createForm(ClientTariffType::class);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $result = $billingApi->changeTariff($form->getData());
                if ($result->isSuccessful()) {
                    //TODO: Наверное другой текст
                    $this->addFlash('success', 'view.personal_account.tariff.change_tariff.success');
                } else {
                    $this->addBillingErrorFlash();
                    $this->get('mbh.form_data_handler')->fillFormByBillingErrors($form, $result->getErrors());
                }
            }
        }

        $tariffsData = $billingApi->getTariffsData();

        return [
            'tariffsData' => $tariffsData,
            'form' => $form->createView()
        ];
    }

    public function updateTariffAction(Request $request)
    {
        $form = $this->createForm(ClientTariffType::class);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $result = $billingApi->changeTariff($form->getData());
                if ($result->isSuccessful()) {
                    //TODO: Наверное другой текст
                    $this->addFlash('success', 'view.personal_account.tariff.change_tariff.success');
                } else {
                    $this->addBillingErrorFlash();
                    $this->get('mbh.form_data_handler')->fillFormByBillingErrors($form, $result->getErrors());
                }
            }
        }
    }

    /**
     * @Template()
     * @Security("is_granted('ROLE_PAYMENTS')")
     * @Route("/payer", name="user_payer")
     * @param Request $request
     * @return array
     */
    public function payerAction(Request $request)
    {
        $payerCompany = $this->get('mbh.client_payer_manager')->getClientPayerCompany();

        $form = $this->createForm(PayerType::class, null, [
            'client' => $this->get('mbh.client_manager')->getClient(),
            'company' => $payerCompany
        ]);

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
     */
    public function paymentsListAction(Request $request)
    {
        $formData = $request->request->get('form');
        $beginDate = isset($formData['begin'])
            ? $this->helper->getDateFromString($formData['begin'])
            : new \DateTime('midnight - 300 days');

        $endDate = isset($formData['end'])
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
     * @Template()
     * @Security("is_granted('ROLE_PAYMENTS')")
     * @Route("/payment_order/{orderId}/payment_systems", name="order_payment_systems", options={"expose"=true})
     * @param $orderId
     * @return array
     */
    public function payOrderModalAction($orderId)
    {
        $billingApi = $this->get('mbh.billing.api');
        $order = $billingApi->getClientOrderById($orderId);
        $paymentSystemsResult = $billingApi->getPaymentSystemsForOrder($order);

        return [
            'paymentTypes' => $paymentSystemsResult->getData(),
            'order' => $order
        ];
    }

    /**
     * @Route("/client_successful_payment", name="client_successful_payment", options={"expose"=true})
     * @Template()
     * @return array
     */
    public function paymentSuccessfulPageAction()
    {
        return [];
    }

    private function addBillingErrorFlash()
    {
        $this->addFlash('error',
            $this->get('translator')->trans('controller.profileController.request_error', [
                '%supportEmail%' => $this->getParameter('support')['email']
            ]));
    }
}
