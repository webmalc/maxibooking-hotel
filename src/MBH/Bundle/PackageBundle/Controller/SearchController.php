<?php

namespace MBH\Bundle\PackageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PackageBundle\Form\SearchType;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;

/**
 * @Route("/search")
 */
class SearchController extends Controller implements CheckHotelControllerInterface
{

    /**
     * Search action
     *
     * @Route("/", name="package_search", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $results = $tariffResults = false;

        $form = $this->createForm(
            new SearchType(), [], [
                'security' => $this->container->get('mbh.hotel.selector'),
                'dm' => $dm,
                'hotel' => $this->get('mbh.hotel.selector')->getSelected(),
                'orderId' => $request->get('order')
            ]
        );

        $beginDate = new \DateTime();
        $beginDate->setTime(0, 0, 0);
        $beginDate->modify($this->container->getParameter('mbh.room.cache.date.modify'));

        return [
            'form' => $form->createView(),
            'beginDate' => $beginDate,
        ];
    }

    /**
     * Search action
     *
     * @Route("/results", name="package_search_results", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function resultsAction(Request $request)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $results = $tariffResults = false;

        $form = $this->createForm(
            new SearchType(), [], [
                'security' => $this->container->get('mbh.hotel.selector'),
                'dm' => $dm,
                'hotel' => $this->get('mbh.hotel.selector')->getSelected()
            ]
        );

        // Validate form
        if ($request->get('s')) {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();

                //Set query
                $query = new SearchQuery();
                $query->begin = $data['begin'];
                $query->end = $data['end'];
                $query->adults = (int)$data['adults'];
                $query->children = (int)$data['children'];
                $query->tariff = $data['tariff'];

                foreach ($data['roomType'] as $id) {
                    if (mb_stripos($id, 'allrooms_') !== false) {
                        $hotel = $dm->getRepository('MBHHotelBundle:Hotel')->find(str_replace('allrooms_', '', $id));

                        if (!$hotel) {
                            continue;
                        }

                        foreach ($hotel->getRoomTypes() as $roomType) {
                            $query->addRoomType($roomType->getId());
                        }
                    } else {
                        $query->addRoomType($id);
                    }
                }

                $results = $this->get('mbh.package.search')->search($query);
                $tariffResults = $this->get('mbh.package.search')->searchTariffs($query);
            }
        }

        return [
            'results' => $results,
            'tariffResults' => $tariffResults
        ];
    }

}
