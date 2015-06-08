<?php

namespace MBH\Bundle\VegaBundle\Controller;

use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="vega_default")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('MBHVegaBundle:Default:index.html.twig', array('name' => 12));
    }

    /**
     * @Route("/{id}/export", name="vega_export")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function exportAction(Tourist $entity)
    {
        //$entity->getPackages()

        $xml = $this->get('mbh.vega.vega_export')->getXML($entity);

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
