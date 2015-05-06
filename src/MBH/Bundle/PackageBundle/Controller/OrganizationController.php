<?php

namespace MBH\Bundle\PackageBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;

use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Form\OrganizationType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class OrganizationController
 * @package MBH\Bundle\PackageBundle\Controller
 *
 * @Route("/organizations")
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class OrganizationController extends Controller
{
    /**
     * @Route("/list/{type}", name="organizations", defaults={"type" = "contragents"})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function indexAction($type = 'contragents')
    {
        return [
            'type' => $type,
            'types' => $this->container->getParameter('mbh.organization.types')
        ];
    }

    /**
     * @Route("/json", name="organization_json", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     */
    public function organizationJsonAction(Request $request)
    {
        /* @var $dm  \Doctrine\ODM\MongoDB\DocumentManager */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $criteria = [];

        $search = $request->get('search');
        $searchValue = $search['value'];

        if($searchValue){
            $searchFields = [
                'name',
                'short_name',
                'phone',
                'director_fio',
                'email',
                'inn',
                'kpp',
                'street',
                'comment'
            ];

            $criteria = [];
            foreach($searchFields as $field){
                $criteria['$or'][] = [$field => new \MongoRegex('/.*' . $searchValue . '.*/ui')];
            }
        }

        $type = $request->get('type');
        if($type)
            $criteria['type'] = $type;

        $sort = null;

        $order = $request->get('order');
        $order = is_array($order) && $order ? $order[0] : null;
        $cols = [1 => 'name', 2 => 'phone', 3 => 'inn', 4 => 'kpp', 5 => 'city', 6 => 'bank', 7 => 'checking_account'];
        $column = array_key_exists((int)$order['column'], $cols) ? $cols[(int)$order['column']] : null;
        if(isset($column) && isset($order['dir']))
            $sort = [$column => $order['dir'] == 'desc' ? 1 : -1];

        $organizations = $dm->getRepository('MBHPackageBundle:Organization')->findBy($criteria, $sort, $request->get('length'), $request->get('start'));

        $data = [];

        foreach($organizations as $organization){
            $hotels = $organization->getHotels();
            $hotelList = [];
            foreach($hotels as $h)
                $hotelList[$h->getId()] = $h->getName();

            $data[] = [
                '<i class="fa fa-users"></i>',
                '<a rel="main" href="'.$this->generateUrl('edit_organization', ['id' => $organization->getId()]).'">'.$organization->getName().'</a>',
                $organization->getInn(),
                $organization->getLocation(),
                $organization->getPhone(),
                $organization->getType() == 'my' ? implode(', ', $hotelList) : nl2br($organization->getComment()),
                '<div class="text-center"><a href="'.$this->generateUrl('edit_organization', ['id' => $organization->getId()]).'" class="btn btn-success btn-xs" data-toggle="tooltip">
                    <i class="fa fa-pencil-square-o"></i>
                 </a>
                <a href="'.$this->generateUrl('organization_delete', ['id' => $organization->getId()]).'" data-text="'.htmlspecialchars($this->get('translator')->trans('organizations.confirm_delete', ['%organization_name%' => $organization->getName()], 'MBHPackageBundle')).'" class="btn btn-danger btn-xs delete-link "" data-toggle="tooltip">
                    <i class="fa fa-trash-o"></i>
                </a><div>'
            ];
        }

        $recordsTotal = $dm->getRepository('MBHPackageBundle:Organization')->createQueryBuilder()->setQueryArray($criteria)->getQuery()->count();

        /*$data = $this->renderView('MBHPackageBundle:Organization:json.html.twig', [
            'organizations' => $organizations,
        ]);*/
        return new JsonResponse([
            'data' => $data,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
        ]);
    }

    /**
     * @Route("/create", name="create_organization")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function createAction(Request $request)
    {
        /* @var $dm  \Doctrine\ODM\MongoDB\DocumentManager */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $organization = new Organization();

        $form = $this->createForm(new OrganizationType($dm), $organization, [
            'typeList' => $this->container->getParameter('mbh.organization.types'),
        ]);

        if ($request->isMethod('PUT')) {
            $form->submit($request);

            if ($form->isValid()) {
                $dm->persist($organization);
                $dm->flush();

                return $this->redirect($this->generateUrl('organizations'));
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/{id}/edit", name="edit_organization")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("organization", class="MBHPackageBundle:Organization")
     * @Template()
     */
    public function editAction(Organization $organization, Request $request)
    {
        /* @var $dm  \Doctrine\ODM\MongoDB\DocumentManager */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $form = $this->createForm(new OrganizationType($dm), $organization, [
            'typeList' => $this->container->getParameter('mbh.organization.types'),
            'id' => $organization->getId()
        ]);

        if ($request->isMethod('PUT')) {
            $form->submit($request);

            if ($form->isValid()) {
                $dm->persist($organization);
                $dm->flush();

                return $this->redirect($this->generateUrl('organizations'));
            }
        }

        return [
            'form' => $form->createView(),
            'organization' => $organization,
        ];
    }


    /**
     * @Route("/{id}/delete", name="organization_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @ParamConverter("organization", class="MBHPackageBundle:Organization")
     * @Template()
     */
    public function deleteAction(Organization $organization)
    {
        /* @var $dm  \Doctrine\ODM\MongoDB\DocumentManager */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->remove($organization);
        $dm->flush();
        return $this->redirect($this->generateUrl('organizations'));
    }
}