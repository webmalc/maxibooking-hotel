<?php

namespace MBH\Bundle\VegaBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;



class DefaultController extends BaseController
{
    /**
     * @Route("/{id}/export", name="vega_export")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
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