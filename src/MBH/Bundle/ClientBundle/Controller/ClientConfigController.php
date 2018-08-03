<?php

namespace MBH\Bundle\ClientBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Form\NotificationConfigType;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\ColorsConfig;
use MBH\Bundle\ClientBundle\Document\Invoice;
use MBH\Bundle\ClientBundle\Document\Moneymail;
use MBH\Bundle\ClientBundle\Document\Payanyway;
use MBH\Bundle\ClientBundle\Document\Paypal;
use MBH\Bundle\ClientBundle\Document\Rbk;
use MBH\Bundle\ClientBundle\Document\RNKB;
use MBH\Bundle\ClientBundle\Document\Robokassa;
use MBH\Bundle\ClientBundle\Document\Stripe;
use MBH\Bundle\ClientBundle\Document\Uniteller;
use MBH\Bundle\ClientBundle\Form\ClientConfigType;
use MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType;
use MBH\Bundle\ClientBundle\Form\PaymentSystemsUrlsType;
use MBH\Bundle\ClientBundle\Form\ColorsType;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\NewRbkHelper;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\RobokassaHelper;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\TinkoffHelper;
use MBH\Bundle\ClientBundle\Lib\PaymentSystem\UnitellerHelper;
use MBH\Bundle\ClientBundle\Service\Notice;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/config")
 */
class ClientConfigController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Main configuration page
     * @Route("/", name="client_config")
     * @Method("GET")
     * @Security("is_granted('ROLE_CLIENT_CONFIG_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $entity = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        $form = $this->createForm(ClientConfigType::class, $entity);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Main configuration page save
     * @Route("/", name="client_config_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_CLIENT_CONFIG_EDIT')")
     * @Template("MBHClientBundle:ClientConfig:index.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAction(Request $request)
    {
        $entity = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();

        if (!$entity) {
            $entity = new ClientConfig();
        }

        $form = $this->createForm(ClientConfigType::class, $entity);

        $previousTimeZone = $entity->getTimeZone();
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!is_null($previousTimeZone)
                && $previousTimeZone != $entity->getTimeZone()
                && (empty($this->getUser()) || $this->getUser()->getUsername() !== 'mb')) {
                $entity->setTimeZone($previousTimeZone);
                $this->addFlash('warning',
                    $this->get('translator')->trans('controller.clientConfig.change_time_zone_contact_support',
                        ['%supportEmail%' => $this->getParameter('support')['email']]))
                ;
            }
            $this->dm->persist($entity);
            $this->dm->flush();

            $this->addFlash('success', 'controller.clientConfig.params_success_save');

            return $this->redirect($this->generateUrl('client_config'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
        ];
    }

    /**
     * @Route("/payment_systems", name="client_payment_systems", options={"expose"=true})
     * @Template()
     * @Security("is_granted('ROLE_CLIENT_CONFIG_EDIT')")
     * @return array
     */
    public function paymentSystemsAction()
    {
        return [
            'config' => $this->clientConfig,
            'paymentSystems' => $this->getParameter('mbh.payment_systems')
        ];
    }

    /**
     * @Method("GET")
     * @Route("/payment_urls", name="client_payment_urls", options={"expose"=true})
     * @Template()
     * @return array|JsonResponse
     */
    public function paymentUrlsAction()
    {
        $form = $this->createForm(PaymentSystemsUrlsType::class, $this->clientConfig);

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Method("POST")
     * @Route("/save_payment_urls", name="client_save_payment_urls", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function savePaymentUrls(Request $request)
    {
        $form = $this->createForm(PaymentSystemsUrlsType::class, $this->clientConfig);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dm->flush();

            return new JsonResponse([
                'success' => true,
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'form' => $this->renderView('@MBHClient/ClientConfig/paymentUrls.html.twig', ['form' => $form->createView()])
        ]);
    }

    /**
     * Payment system configuration page
     * @Route("/payment_system_form/{paymentSystemName}", name="client_payment_system_form")
     * @Method("GET")
     * @Security("is_granted('ROLE_CLIENT_CONFIG_VIEW')")
     * @Template()
     * @return array
     */
    public function paymentSystemFormAction($paymentSystemName = null)
    {
        $form = $this->createForm(ClientPaymentSystemType::class, $this->clientConfig, [
            'entity' => $this->clientConfig,
            'paymentSystemName' => $paymentSystemName
        ]);

        return [
            'entity' => $this->clientConfig,
            'form' => $form->createView(),
            'logs' => $this->logs($this->clientConfig),
            'paymentSystemName' => $paymentSystemName
        ];
    }

    /**
     * Payment system configuration save
     * @Route("/payment_system/save", name="client_payment_system_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_CLIENT_CONFIG_EDIT')")
     * @Template("MBHClientBundle:ClientConfig:paymentSystemForm.html.twig")
     * @param $request Request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws Exception
     */
    public function paymentSystemSaveAction(Request $request)
    {
        $config = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        $paymentSystemName = $request->query->get('paymentSystemName');

        $form = $this->createForm(ClientPaymentSystemType::class, $config, [
            'entity' => $config,
            'paymentSystemName' => $paymentSystemName
        ]);

        $form->handleRequest($request);
        $paymentSystemName = $request->request->get($form->getName())['paymentSystem'] ?? $paymentSystemName;

        if ($form->isValid()) {
            switch ($paymentSystemName) {
                case 'robokassa':
                    $config->setRobokassa(RobokassaHelper::instance($form));
                    break;
                case 'payanyway':
                    $payanyway = new Payanyway();
                    $payanyway->setPayanywayKey($form->get('payanywayKey')->getData())
                        ->setPayanywayMntId($form->get('payanywayMntId')->getData());
                    $config->setPayanyway($payanyway);
                    break;
                case 'moneymail':
                    $moneymail = new Moneymail();
                    $moneymail->setMoneymailShopIDP($form->get('moneymailShopIDP')->getData())
                        ->setMoneymailKey($form->get('moneymailKey')->getData());
                    $config->setMoneymail($moneymail);
                    break;
                case 'uniteller':
                    $config->setUniteller(UnitellerHelper::instance($form));
                    break;
                case 'rbk':
                    $rbk = new Rbk();
                    $rbk->setRbkEshopId($form->get('rbkEshopId')->getData())
                        ->setRbkSecretKey($form->get('rbkSecretKey')->getData());
                    $config->setRbk($rbk);
                    break;
                case 'paypal':
                    $paypal = new Paypal();
                    $paypal->setPaypalLogin($form->get('paypalLogin')->getData());
                    $config->setPaypal($paypal);
                    break;
                case 'rnkb':
                    $rnkb = new RNKB();
                    $rnkb->setKey($form->get('rnkbKey')->getData());
                    $rnkb->setRnkbShopIDP($form->get('rnkbShopIDP')->getData());
                    $config->setRnkb($rnkb);
                    break;
                case 'invoice':
                    $invoice = (new Invoice())->setInvoiceDocument($form->get('invoiceDocument')->getData());
                    $config->setInvoice($invoice);
                    break;
                case 'stripe':
                    $stripe = (new Stripe())
                        ->setPublishableToken($form->get('stripePubToken')->getData())
                        ->setSecretKey($form->get('stripeSecretKey')->getData());
                    $config->setStripe($stripe);
                    break;
                case 'newRbk':
                    $config->setNewRbk(NewRbkHelper::instance($form));
                    break;
                case 'tinkoff':
                    $config->setTinkoff(TinkoffHelper::instance($form));
                    break;
                default:
                    throw new Exception('Incorrect name of payment system!');
            }
            $config->addPaymentSystem($paymentSystemName);

            $this->dm->persist($config);
            $this->dm->flush();

            $this->addFlash('success', 'controller.clientConfig.params_success_save');

            return $this->redirect($this->generateUrl('client_payment_systems'));
        }

        return [
            'entity'            => $config,
            'form'              => $form->createView(),
            'paymentSystemName' => $paymentSystemName,
            'logs'              => $this->logs($config),
        ];
    }

    /**
     * @Route("payment_system/remove", name="remove_payment_system")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removePaymentSystemAction(Request $request)
    {
        $paymentSystemName = $request->query->get('paymentSystemName');
        $this->clientConfig->removePaymentSystem($paymentSystemName);
        $this->dm->flush();

        return $this->redirectToRoute('client_payment_systems');
    }

    /**
     * @Security("is_granted('ROLE_CLIENT_CONFIG_EDIT')")
     * @Route("/change_room_type_enableable_mode/{disableMode}/{route}", name="change_room_type_enableable_mode", options={"expose"=true})
     * @param $disableMode
     * @param $route
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeRoomTypeEnableableModeAction($disableMode, $route)
    {
        $disableModeBool = $disableMode == 'true';
        $this->dm->getRepository('MBHClientBundle:ClientConfig')->changeDisableableMode($disableModeBool);

        return $this->redirectToRoute($route);
    }

    /**
     * @Template()
     * @param Request $request
     * @Security("is_granted('ROLE_CLIENT_CONFIG_EDIT')")
     * @Route("/color_settings", name="color_settings")
     * @return array
     */
    public function colorSettingsAction(Request $request)
    {
        $entity = $this->dm->getRepository('MBHClientBundle:ColorsConfig')->fetchConfig();
        $form = $this->createForm(ColorsType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->dm->flush();
            $this->addFlash('success', 'controller.color_settings_action.config_successful_saved');
        }

        return [
            'form' => $form->createView(),
            'entity' => $entity,
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/color_settings/reset", name="reset_color_settings")
     * @Method({"GET"})
     * @Security("is_granted('ROLE_CLIENT_CONFIG_EDIT')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resetSettingsAction()
    {
        $config = $this->dm->getRepository('MBHClientBundle:ColorsConfig')->fetchConfig();
        $this->dm->remove($config);
        $config = new ColorsConfig();
        $this->dm->persist($config);
        $this->dm->flush();
        $this->addFlash('success', 'controller.color_settings_action.config_successful_reset');

        return $this->redirectToRoute('color_settings');
    }
}
