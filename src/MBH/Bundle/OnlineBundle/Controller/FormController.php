<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\OnlineBundle\Form\FormType;

/**
 * @Route("/form")
 */
class FormController extends Controller  implements CheckHotelControllerInterface
{
    /**
     * Main configuration page
     * @Route("/", name="online_form")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity = $dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy([]);

        ($dm->getRepository('MBHHotelBundle:Hotel')->findOneBy(['isHostel' => true])) ? $hostel = true : $hostel = false;

        $form = $this->createForm(
            new FormType(),
            $entity,
            [
                'paymentTypes' => $this->container->getParameter('mbh.online.form')['payment_types'],
                'isHostel' => $hostel,
                'paymentSystem' => $this->container->getParameter('mbh.online.form')['payment_system']
            ]
        );

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'config' => $this->container->getParameter('mbh.online.form')
        ];
    }

    /**
     * Main configuration page save
     * @Route("/", name="online_form_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHOnlineBundle:Form:index.html.twig")
     */
    public function saveAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity = $dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy([]);

        if (!$entity) {
            $entity = new FormConfig();
        }

        ($dm->getRepository('MBHHotelBundle:Hotel')->findOneBy(['isHostel' => true])) ? $hostel = true : $hostel = false;

        $form = $this->createForm(
            new FormType(),
            $entity,
            [
                'paymentTypes' => $this->container->getParameter('mbh.online.form')['payment_types'],
                'isHostel' => $hostel,
                'paymentSystem' => $this->container->getParameter('mbh.online.form')['payment_system']
            ]
        );

        $form->submit($request);

        if ($form->isValid()) {

            if($hostel) {
                $entity->setTourists(false);
            }
            $dm->persist($entity);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', 'Настройки успешно сохранены.')
            ;

            return $this->redirect($this->generateUrl('online_form'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'config' => $this->container->getParameter('mbh.online.form')
        ];
    }
}
