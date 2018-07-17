<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ClientBundle\Lib\FMSDictionaries;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\BirthPlace;
use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Form\AddressObjectDecomposedType;
use MBH\Bundle\PackageBundle\Form\DocumentRelationType;
use MBH\Bundle\PackageBundle\Form\SearchType;
use MBH\Bundle\PackageBundle\Form\TouristType;
use MBH\Bundle\PackageBundle\Document\SearchQuery;
use MBH\Bundle\BillingBundle\Lib\Model\Country;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
        $query->range = $this->clientConfig ? $this->clientConfig->getSearchDates() : 0;
        $form = $this->createForm(SearchType::class, $query, [
            'security' => $this->container->get('mbh.hotel.selector'),
            'dm' => $this->dm,
            'hotel' => $this->hotel,
            'orderId' => $request->get('order'),
            'roomManager' => $this->manager,
        ]);

        $tourist = new Tourist();
        $tourist->setDocumentRelation(new DocumentRelation());
        $tourist->setBirthplace(new BirthPlace());
        $tourist->setCitizenshipTld(Country::RUSSIA_TLD);
        $tourist->getDocumentRelation()->setType(FMSDictionaries::RUSSIAN_PASSPORT_ID);

        return [
            'form' => $form->createView(),
            'touristForm' => $this->createForm(TouristType::class, null,
                ['genders' => $this->container->getParameter('mbh.gender.types')])
                ->createView(),
            'documentForm' => $this->createForm(DocumentRelationType::class, $tourist)
                ->createView(),
            'addressForm' => $this->createForm(AddressObjectDecomposedType::class, $tourist->getAddressObjectDecomposed())
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
        $specials = null;
        $form = $this->createForm(SearchType::class, $query, [
            'security' => $this->container->get('mbh.hotel.selector'),
            'dm' => $this->dm,
            'hotel' => $this->hotel,
            'roomManager' => $this->manager
        ]);
        $groupedResult = [];

        // Validate form
        if ($request->get('s')) {
            $form->handleRequest($request);

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

                $search = $this->get('mbh.package.search')
                    ->setAdditionalDates($query->range)
                    ->setWithTariffs();
                $specials = $search->searchSpecials($query)->toArray();
                /** store query in db */
                $query->setSave(true);
                $groupedResult = $search->search($query);

            } else {
                $errors = $form->getErrors();
            }
        }

        return [
            'results' => $groupedResult,
            'specials' => $specials,
            'query' => $query,
            'errors' => isset($errors) ? $errors : null,
            'facilities' => $this->get('mbh.facility_repository')->getAll(),
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons')
        ];
    }

}
