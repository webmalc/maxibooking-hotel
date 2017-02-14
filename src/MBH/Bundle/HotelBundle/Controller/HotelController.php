<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\HotelContactInformation;
use MBH\Bundle\HotelBundle\Form\HotelExtendedType;
use MBH\Bundle\HotelBundle\Form\HotelType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HotelController extends Controller
{
    /**
     * Select hotel.
     *
     * @Route("/notfound", name="hotel_not_found")
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     * @Template()
     */
    public function notFoundAction()
    {
        return [];
    }

    /**
     * Select hotel.
     *
     * @Route("/{id}/select", name="hotel_select")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_VIEW')")
     */
    public function selectHotelAction(Request $request, $id)
    {
        $this->get('mbh.hotel.selector')->setSelected($id);

        if ($request->get('url')) {
            return $this->redirect($request->get('url'));
        }

        return $this->redirect($this->generateUrl('_welcome'));
    }

    /**
     * Lists all entities.
     *
     * @Route("/", name="hotel")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHHotelBundle:Hotel')->createQueryBuilder('s')
            ->sort('fullTitle', 'asc')
            ->getQuery()
            ->execute();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="hotel_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Hotel();
        $form = $this->createForm(HotelType::class, $entity);

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="hotel_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_HOTEL_NEW')")
     * @Template("MBHHotelBundle:Hotel:new.html.twig")
     * @see HotelManager::create
     */
    public function createAction(Request $request)
    {
        $entity = new Hotel();
        $form = $this->createForm(HotelType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->uploadFile();

            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.hotelController.record_created_success'))
            ;

            //todo: create services
            $console = $this->container->get('kernel')->getRootDir() . '/../bin/console ';
            $process = new \Symfony\Component\Process\Process('nohup php ' . $console . 'mbh:base:fixtures --no-debug > /dev/null 2>&1 &');
            $process->run();

            return $this->afterSaveRedirect('hotel', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}", name="hotel_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @Template("MBHHotelBundle:Hotel:edit.html.twig")
     * @ParamConverter("entity", class="MBHHotelBundle:Hotel")
     */
    public function updateAction(Request $request, Hotel $entity)
    {
        $form = $this->createForm(HotelType::class, $entity);
        $form->handleRequest($request);
        if ($form->isValid()) {

            $entity->uploadFile();

            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                    ->set('success', $this->get('translator')->trans('controller.hotelController.record_edited_success'))
            ;
            return $this->afterSaveRedirect('hotel', $entity->getId());
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="hotel_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @Template()
     * @param Hotel $entity
     * @return array
     */
    public function editAction(Hotel $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(HotelType::class, $entity, [
            'imageUrl' => $entity->getLogoUrl(),
            'removeImageUrl' => $this->generateUrl('hotel_delete_logo', ['id' => $entity->getId()])
        ]);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     *
     * @Route("/{id}/delete/logo", name="hotel_delete_logo")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @param Hotel $entity
     * @return Response
     */
    public function deleteLogoAction(Hotel $entity)
    {
        if($entity->getFile() || $entity->getLogo()) {
            $entity->setLogo(null);
            $entity->deleteFile();

            $this->dm->persist($entity);
            $this->dm->flush();
        }
        return $this->redirect($this->generateUrl('hotel_edit', ['id' => $entity->getId()]));
    }

    /**
     * Displays a form to edit extended config of an existing entity.
     *
     * @Route("/{id}/edit/extended", name="hotel_edit_extended")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @Template()
     * @param Hotel $entity
     * @return Response
     */
    public function extendedAction(Hotel $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(HotelExtendedType::class, $entity, [
            'config' => $this->container->getParameter('mbh.hotel'),
        ]);
        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Save extended config of an existing entity.
     *
     * @Route("/{id}/edit/extended", name="hotel_edit_extended_save")
     * @Method("POST")
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @Template("MBHHotelBundle:Hotel:extended.html.twig")
     * @param Hotel $entity
     * @return array
     */
    public function extendedUpdateAction(Request $request, Hotel $entity)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($entity)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(HotelExtendedType::class, $entity, [
            'config' => $this->container->getParameter('mbh.hotel'),
            'method' => Request::METHOD_POST
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.hotelController.record_edited_success'))
            ;

            return $this->afterSaveRedirect('hotel', $entity->getId(), [], '_edit_extended');
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * @param Request $request
     * @param Hotel $hotel
     * @Security("is_granted('ROLE_HOTEL_EDIT')")
     * @Route("/{id}/edit/contact", name="hotel_contact_information")
     * @Template()
     * @return array
     */
    public function contactInformationAction(Request $request, Hotel $hotel)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($hotel)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(HotelContactInformation::class, $hotel);


        return [
            'entity' => $hotel,
            'form' => $form->createView(),
            'logs' => $this->logs($hotel)
        ];
    }

    /**
     * Get city by query
     *
     * @Route("/city/{id}", name="hotel_city", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_CITY_VIEW')")
     * @return JsonResponse
     */
    public function cityAction(Request $request, $id = null)
    {
        if (empty($id) && empty($request->get('query'))) {
            return new JsonResponse([]);
        }

        if (!empty($id)) {
            $city =  $this->dm->getRepository('MBHHotelBundle:City')->find($id);

            if ($city) {
                return new JsonResponse([
                    'id' => $city->getId(),
                    'text' => $city->getCountry()->getTitle() . ', ' . $city->getRegion()->getTitle() . ', ' .$city->getTitle()
                ]);
            }
        }

        $cities = $this->dm->getRepository('MBHHotelBundle:City')->createQueryBuilder('q')
            ->field('title')->equals(new \MongoRegex('/.*' . $request->get('query') . '.*/i'))
            ->getQuery()
            ->execute()
        ;

        $data = [];

        foreach ($cities as $city) {
            $data[] = [
                'id' => $city->getId(),
                'text' => $city->getCountry()->getTitle() . ', ' . $city->getRegion()->getTitle() . ', ' .$city->getTitle()
            ];
        }

        $regions = $this->dm->getRepository('MBHHotelBundle:Region')->createQueryBuilder('q')
            ->field('title')->equals(new \MongoRegex('/.*' . $request->get('query') . '.*/i'))
            ->getQuery()
            ->execute()
        ;

        foreach ($regions as $region) {
            foreach ($region->getCities() as $city) {


                $data[] = [
                    'id' => $city->getId(),
                    'text' => $city->getCountry()->getTitle() . ', ' . $city->getRegion()->getTitle() . ', ' .$city->getTitle()
                ];
            }
        }

        return new JsonResponse(['results' => $data]);
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="hotel_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_HOTEL_DELETE')")
     */
    public function deleteAction($id)
    {
        $response = $this->deleteEntity($id, 'MBHHotelBundle:Hotel', 'hotel');
        return $response;
    }
}
