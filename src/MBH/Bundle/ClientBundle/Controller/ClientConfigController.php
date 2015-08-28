<?php

namespace MBH\Bundle\ClientBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\Moneymail;
use MBH\Bundle\ClientBundle\Document\Payanyway;
use MBH\Bundle\ClientBundle\Document\Robokassa;
use MBH\Bundle\ClientBundle\Document\Uniteller;
use MBH\Bundle\ClientBundle\Form\ClientConfigType;
use MBH\Bundle\ClientBundle\Form\ClientPaymentSystemType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

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
        $entity = $this->dm->getRepository('MBHClientBundle:ClientConfig')->findOneBy([]);
        $form = $this->createForm(new ClientConfigType(), $entity);

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
     */
    public function saveAction(Request $request)
    {
        $entity = $this->dm->getRepository('MBHClientBundle:ClientConfig')->findOneBy([]);

        if (!$entity) {
            $entity = new ClientConfig();
        }

        $form = $this->createForm(new ClientConfigType(), $entity);

        $form->submit($request);

        if ($form->isValid()) {

            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.clientConfig.params_success_save'));

            return $this->redirect($this->generateUrl('client_config'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
        ];
    }

    /**
     * Payment system configuration page
     * @Route("/payment_system", name="client_payment_system")
     * @Method("GET")
     * @Security("is_granted('ROLE_CLIENT_CONFIG_VIEW')")
     * @Template()
     */
    public function paymentSystemAction()
    {
        $entity = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();

        $form = $this->createForm(new ClientPaymentSystemType(), $entity, [
            'paymentTypes' => $this->container->getParameter('mbh.payment_systems'),
            'entity' => $entity,
            'change' => $this->container->getParameter('mbh.payment_systems.change'),
            'default' => $this->container->getParameter('mbh.payment_systems.default'),
        ]);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Payment system configuration save
     * @Route("/payment_system/save", name="client_payment_system_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_CLIENT_CONFIG_EDIT')")
     * @Template("MBHClientBundle:ClientConfig:paymentSystem.html.twig")
     * @param $request Request
     * @return array
     */
    public function paymentSystemSaveAction(Request $request)
    {
        $entity = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();

        $form = $this->createForm(new ClientPaymentSystemType(), $entity, [
            'paymentTypes' => $this->container->getParameter('mbh.payment_systems'),
            'entity' => $entity,
            'change' => $this->container->getParameter('mbh.payment_systems.change'),
            'default' => $this->container->getParameter('mbh.payment_systems.default'),
        ]);

        $form->submit($request);

        if ($form->isValid()) {

            switch ($entity->getPaymentSystem()) {
                case 'robokassa':
                    $robokassa = new Robokassa();
                    $robokassa->setRobokassaMerchantLogin($form->get('robokassaMerchantLogin')->getData())
                        ->setRobokassaMerchantPass1($form->get('robokassaMerchantPass1')->getData())
                        ->setRobokassaMerchantPass2($form->get('robokassaMerchantPass2')->getData());
                    $entity->setRobokassa($robokassa);
                    break;
                case 'payanyway':
                    $payanyway = new Payanyway();
                    $payanyway->setPayanywayKey($form->get('payanywayKey')->getData())
                        ->setPayanywayMntId($form->get('payanywayMntId')->getData());
                    $entity->setPayanyway($payanyway);
                    break;
                case 'moneymail':
                    $moneymail = new Moneymail();
                    $moneymail->setMoneymailShopIDP($form->get('moneymailShopIDP')->getData())
                        ->setMoneymailKey($form->get('moneymailKey')->getData());
                    $entity->setMoneymail($moneymail);
                    break;
                case 'uniteller':
                    $uniteller = new Uniteller();
                    $uniteller->setUnitellerShopIDP($form->get('unitellerShopIDP')->getData())
                        ->setUnitellerPassword($form->get('unitellerPassword')->getData());
                    $entity->setUniteller($uniteller);
                    break;
                default:
                    break;
            }

            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.clientConfig.params_success_save'));

            return $this->redirect($this->generateUrl('client_payment_system'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

}
