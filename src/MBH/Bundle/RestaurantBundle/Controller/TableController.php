<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 05.07.16
 * Time: 14:25
 */

namespace MBH\Bundle\RestaurantBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\RestaurantBundle\Document\Table;
use MBH\Bundle\RestaurantBundle\Form\TableType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @Route("tables") */

class TableController extends BaseController
{
    /**
     * @Route("/", name="restaurant_table_list")
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_VIEW')")
     * @Template()
     */
    public function indexAction()
    {
        $entities = $this->dm->getRepository('MBHRestaurantBundle:Table')->createQueryBuilder('q')
            ->field('hotel.id')->equals($this->hotel->getId())
            ->sort('id', 'asc')
            ->getQuery()
            ->execute();
        return [
            'entities' => $entities
        ];
    }

    /**
     * @Route("/new", name="restaurant_table_new")
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_NEW')")
     * @Template()
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function newTableAction(Request $request)
    {
        $table = new Table();
        $table->setHotel($this->hotel);

        $dishes = $this->dm->getRepository('MBHRestaurantBundle:Table')->findAll();

        $form = $this->createForm(new TableType(), $table);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->dm->persist($table);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set(
                'success',
                'restaurant.table.common.addsuccess'
            );

            return $this->isSavedRequest() ?
                $this->redirectToRoute('restaurant_table_edit', ['id' => $table->getId()]) :
                $this->redirectToRoute('restaurant_table_list');
        }

        return [
            'form' => $form->createView(),
            'table' => $table,
            'dishes' => $dishes
        ];
    }

    /**
     * @Route("/{id}/edit", name="restaurant_table_edit")
     * @Method({"GET","POST"})
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_EDIT')")
     * @Template()
     * @ParamConverter(class="MBHRestaurantBundle:Table")
     * @param Request $request
     * @param Table $table
     * @return array|RedirectResponse
     */
    public function editTableAction(Request $request, Table $table)
    {
        if (!$this->container->get('mbh.hotel.selector')->checkPermissions($table->getHotel())) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new TableType(), $table);
        $form->handleRequest($request);

        $dishes = $this->dm->getRepository('MBHRestaurantBundle:DishMenuItem')->findAll();

        if ($form->isValid() && $form->isSubmitted()) {
            $this->dm->persist($table);
            $this->dm->flush();

            $request->getSession()->getFlashBag()->set(
                'success',
                'restaurant.table.common.editsuccess'
            );

            return $this->isSavedRequest() ?
                $this->redirectToRoute('restaurant_table_edit', ['id' => $table->getId()]) :
                $this->redirectToRoute('restaurant_table_list');
        }

        return [
            'table' =>  $table,
            'form' => $form->createView(),
            'logs' => $this->logs($table),
            'dishes' => $dishes
        ];
    }

    /**
     * @Route("/{id}/delete", name="restaurant_table_delete")
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_DELETE')")
     * @ParamConverter(class="MBHRestaurantBundle:Table")
     * @param Request $request
     * @param Table $table
     * @return array|RedirectResponse
     */
    public function deleteTableAction(Request $request, Table $table)
    {
        try {
            if (!$this->container->get('mbh.hotel.selector')->checkPermissions($table->getHotel())) {
                throw $this->createNotFoundException();
            }
            $this->dm->remove($table);
            $this->dm->flush($table);

            $request->getSession()->getFlashBag()->set('success', 'Запись успешно удалена.');
        } catch (DeleteException $e) {
            $request->getSession()->getFlashBag()->set('danger', $e->getMessage());
        }

        return $this->redirectToRoute('restaurant_table_list');
    }

    /**
     * save entries prices
     *
     * @Route("/quicksave", name="restaurant_table_quicksave")
     * @Method("GET")
     * @Security("is_granted('ROLE_RESTAURANT_TABLE_EDIT')")
     *
     */
    public function quickSaveAction(Request $request)
    {
        $entries = $request->get('entries');

        $tableRepository = $this->dm->getRepository('MBHRestaurantBundle:Table');

        $success = true;

        foreach ($entries as $id => $data) {
            $entity = $tableRepository->find($id);
            $isEnabled = $data['is_enabled'] ?? false;

            if (!$entity || !$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
                continue;
            }

            $entity->setIsEnabled((boolean)$isEnabled);

            $validator = $this->get('validator');
            $errors = $validator->validate($entity);
            if (count($errors) > 0) {
                $success = false;
                continue;
            }

            $this->dm->persist($entity);
            $this->dm->flush();
        };

        $flashBag = $request->getSession()->getFlashBag();

        $success ?
            $flashBag->set('success', 'Столики успешно отредактированы.'):
            $flashBag->set('danger', 'Внимание, не все параметры сохранены успешно');


        return $this->redirectToRoute('restaurant_table_list');
    }


}