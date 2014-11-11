<?php

namespace MBH\Bundle\ClientBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Form\ClientConfigType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

/**
 * @Route("/config")
 */
class ClientConfigController extends Controller  implements CheckHotelControllerInterface
{
    /**
     * Main configuration page
     * @Route("/", name="client_config")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity = $dm->getRepository('MBHClientBundle:ClientConfig')->findOneBy([]);

        $form = $this->createForm(
            new ClientConfigType(),
            $entity
        );

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
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHClientBundle:ClientConfig:index.html.twig")
     */
    public function saveAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity = $dm->getRepository('MBHClientBundle:ClientConfig')->findOneBy([]);

        if (!$entity) {
            $entity = new ClientConfig();
        }

        $form = $this->createForm(
            new ClientConfigType(),
            $entity
        );

        $form->submit($request);

        if ($form->isValid()) {

            $dm->persist($entity);
            $dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', 'Параметры успешно сохранены.')
            ;

            return $this->redirect($this->generateUrl('client_config'));
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
        ];
    }
}
