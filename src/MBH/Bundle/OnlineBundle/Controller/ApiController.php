<?php

namespace MBH\Bundle\OnlineBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * Online form js
     * @Route("/form", name="online_form_get", defaults={"_format"="js"})
     * @Method("GET")
     * @Template("")
     */
    public function getFormAction()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $config = $this->container->getParameter('mbh.online.form');
        $formConfig = $dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy([]);

        if(!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        $hotels = $dm->getRepository('MBHHotelBundle:Hotel')
            ->createQueryBuilder('q')
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

        $text = $this->get('templating')->render('MBHOnlineBundle:Api:form.html.twig', [
            'config' => $config,
            'formConfig' => $formConfig,
            'hotels' => $hotels
        ]);

        return [
            'styles' => $this->get('templating')->render('MBHOnlineBundle:Api:form.css.twig'),
            'text' => $text
        ];
    }

    /**
     * Results js
     * @Route("/results", name="online_form_results", defaults={"_format"="js"})
     * @Method("GET")
     * @Template("")
     */
    public function getResultsAction()
    {
        return [
            'styles' => $this->get('templating')->render('MBHOnlineBundle:Api:results.css.twig'),
            'urls' => [
                'table' => $this->generateUrl('online_form_results_table', [], true )
            ]
        ];
    }

    /**
     * Results table
     * @Route("/results/table", name="online_form_results_table", options={"expose"=true})
     * @Method("GET")
     * @Template("")
     */
    public function getResultsTableAction(Request $request)
    {
        $this->addAccessControlAllowOriginHeaders($this->container->getParameter('mbh.online.form')['sites']);
        $helper = $this->get('mbh.helper');

        $query = new SearchQuery();
        $query->begin = $helper->getDateFromString($request->get('begin'));
        $query->end = $helper->getDateFromString($request->get('end'));
        $query->adults = (int) $request->get('adults');
        $query->children = (int) $request->get('children');
        $query->tariff = $request->get('tariff');
        $query->addRoomType($request->get('roomType'));

        $results = $this->get('mbh.package.search')->search($query);

        $hotels  = [];
        foreach ($results as $result) {
            $hotels[] = $result->getRoomType()->getHotel()->getId();
        }

        return [
            'results' => $results,
            'config' => $this->container->getParameter('mbh.online.form'),
            'hotels' => array_unique($hotels)
        ];
    }
}
