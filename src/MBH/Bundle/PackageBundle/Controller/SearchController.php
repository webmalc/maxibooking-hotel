<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\PackageBundle\Document\BirthPlace;
use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Form\TouristType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use MBH\Bundle\PackageBundle\Form\SearchType;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;

/**
 * @Route("/search")
 */
class SearchController extends Controller implements CheckHotelControllerInterface
{

    /**
     * @var RoomTypeManager
     */
    private $manager;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->manager = $this->get('mbh.hotel.room_type_manager');
    }

    /**
     * Search action
     *
     * @Route("/", name="package_search", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_SEARCH')")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $query = new SearchQuery();
        $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        $query->range = $clientConfig ? $clientConfig->getSearchDates() : 0;
        $form = $this->createForm(new SearchType(), $query, [
            'security' => $this->container->get('mbh.hotel.selector'),
            'dm' => $this->dm,
            'hotel' => $this->hotel,
            'orderId' => $request->get('order'),
            'roomManager' => $this->manager
        ]);

        $tourist = new Tourist();
        $tourist->setDocumentRelation(new DocumentRelation());
        $tourist->setBirthplace(new BirthPlace());
        $tourist->setCitizenship($this->dm->getRepository('MBHVegaBundle:VegaState')->findOneByOriginalName('РОССИЯ'));
        $tourist->getDocumentRelation()->setType('vega_russian_passport');

        return [
            'form' => $form->createView(),
            'touristForm' => $this->createForm(new TouristType(), null,
                ['genders' => $this->container->getParameter('mbh.gender.types')])
                ->createView(),
            'documentForm' => $this->createForm('mbh_document_relation', $tourist)
                ->createView(),
            'addressForm' => $this->createForm('mbh_address_object_decomposed', $tourist->getAddressObjectDecomposed())
                ->createView()
        ];
    }

    /**
     * Search action
     *
     * @Route("/results", name="package_search_results", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_SEARCH')")
     * @Template()
     */
    public function resultsAction(Request $request)
    {
        $query = new SearchQuery();
        $query->accommodations = true;
        $form = $this->createForm(new SearchType(), $query, [
            'security' => $this->container->get('mbh.hotel.selector'),
            'dm' => $this->dm,
            'hotel' => $this->hotel,
            'roomManager' => $this->manager
        ]);

        // Validate form
        if ($request->get('s')) {
            $form->submit($request);

            if ($form->isValid()) {
                $query->setChildrenAges(
                    !empty($request->get('s')['children_age']) ? $request->get('s')['children_age'] : []
                );

                $hotelRepository = $this->dm->getRepository('MBHHotelBundle:Hotel');
				
                foreach ($form['roomType']->getData() as $id) {
                    if (mb_stripos($id, 'allrooms_') !== false) {
                        $hotel = $hotelRepository->find(str_replace('allrooms_', '', $id));

                        if (!$hotel) {
                            continue;
                        }
                        foreach ($this->manager->getRooms($hotel) as $roomType) {
                            $query->addRoomType($roomType->getId());
                        }
                    } else {
                        $query->addRoomType($id);
                    }
                }

                $groupedResult = $this->get('mbh.package.search')
                    ->setAdditionalDates($query->range)
                    ->setWithTariffs()
                    ->search($query);
            } else {
                $errors = $form->getErrors();
                $groupedResult = [];
            }
        }

        return [
            'results' => $groupedResult,
            'query' => $query,
            'errors' => isset($errors) ? $errors : null,
            'facilities' => $this->get('mbh.facility_repository')->getAll(),
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons')
        ];
    }

}
