<?php

namespace MBH\Bundle\HotelBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\HotelType;
use MBH\Bundle\HotelBundle\Form\HotelExtendedType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class HotelController extends Controller
{
    /**
     * Select hotel.
     *
     * @Route("/notfound", name="hotel_not_found")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
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
     * @Security("is_granted('ROLE_USER')")
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
     * @Security("is_granted('ROLE_ADMIN')")
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
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Hotel();
        $form = $this->createForm(new HotelType(), $entity);

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="hotel_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:Hotel:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Hotel();
        $form = $this->createForm(new HotelType(), $entity);
        $form->submit($request);

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
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:Hotel:edit.html.twig")
     * @ParamConverter("entity", class="MBHHotelBundle:Hotel")
     */
    public function updateAction(Request $request, Hotel $entity)
    {
        $form = $this->createForm(new HotelType(), $entity);
        $form->submit($request);

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
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @param Hotel $entity
     * @return array
     */
    public function editAction(Hotel $entity)
    {
        $form = $this->createForm(new HotelType(), $entity, [
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
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/delete/logo", name="hotel_delete_logo")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @param Hotel $entity
     * @return Response
     */
    public function deleteLogo(Hotel $entity)
    {
        if($entity->getFile()) {
            $entity->setLogo(null);
            $entity->deleteFile();
        }
        return $this->redirect($this->generateUrl('hotel_edit', ['id' => $entity->getId()]));
    }

    /**
     * Displays a form to edit extended config of an existing entity.
     *
     * @Route("/{id}/edit/extended", name="hotel_edit_extended")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     * @param Hotel $entity
     * @return Response
     */
    public function extendedAction(Hotel $entity)
    {
        $form = $this->createForm(new HotelExtendedType($this->dm), $entity, [
            'city' => $entity->getCity(),
            'config' => $this->container->getParameter('mbh.hotel')
        ]);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        );
    }

    /**
     * Save extended config of an existing entity.
     *
     * @Route("/{id}/edit/extended", name="hotel_edit_extended_save")
     * @Method("PUT")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHHotelBundle:Hotel:extended.html.twig")
     * @param Hotel $entity
     * @return array
     */
    public function extendedSaveAction(Request $request, Hotel $entity)
    {
        $form = $this->createForm(new HotelExtendedType($this->dm), $entity, [
            'city' => $entity->getCity(),
            'config' => $this->container->getParameter('mbh.hotel'),
            'isHostel' => in_array('hostel', $entity->getType()),
        ]);

        $form->submit($request);

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
     * Get city by query
     *
     * @Route("/city/{id}", name="hotel_city", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @return JsonResponse
     */
    public function cityAction(Request $request, $id = null)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        if (empty($id) && empty($request->get('query'))) {
            return new JsonResponse([]);
        }

        if (!empty($id)) {
            $city =  $dm->getRepository('MBHHotelBundle:City')->find($id);

            if ($city) {
                return new JsonResponse([
                    'id' => $city->getId(),
                    'text' => $city->getCountry()->getTitle() . ', ' . $city->getRegion()->getTitle() . ', ' .$city->getTitle()
                ]);
            }
        }

        $cities = $dm->getRepository('MBHHotelBundle:City')->createQueryBuilder('q')
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

        $regions = $dm->getRepository('MBHHotelBundle:Region')->createQueryBuilder('q')
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

        return new JsonResponse($data);
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="hotel_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteAction($id)
    {
        $response = $this->deleteEntity($id, 'MBHHotelBundle:Hotel', 'hotel');
        return $response;
    }
}
