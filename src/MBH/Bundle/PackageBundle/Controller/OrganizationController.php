<?php

namespace MBH\Bundle\PackageBundle\Controller;


use Doctrine\MongoDB\Query\Expr;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;

use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Form\OrganizationType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

 */
class OrganizationController extends Controller
{
    /**
     * @Route("/list/{type}", name="organizations", defaults={"type" = "contragents"})
     * @Method("GET")
     * @Security("is_granted('ROLE_ORGANIZATION_VIEW')")
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
     * @Security("is_granted('ROLE_ORGANIZATION_VIEW')")
     */
    public function organizationJsonAction(Request $request)
    {
        $criteria = [];

        $search = $request->get('search');
        $searchValue = $search['value'];

        if ($searchValue) {
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
            foreach ($searchFields as $field) {
                $criteria['$or'][] = [$field => new \MongoRegex('/.*'.$searchValue.'.*/ui')];
            }
        }

        $type = $request->get('type');
        if ($type) {
            $criteria['type'] = $type;
        }

        $sort = null;

        $order = $request->get('order');
        $order = is_array($order) && $order ? $order[0] : null;
        $cols = [1 => 'name', 2 => 'phone', 3 => 'inn', 4 => 'kpp', 5 => 'city', 6 => 'bank', 7 => 'checking_account'];
        $column = array_key_exists((int)$order['column'], $cols) ? $cols[(int)$order['column']] : null;
        if (isset($column) && isset($order['dir'])) {
            $sort = [$column => $order['dir'] == 'desc' ? 1 : -1];
        }

        $organizationRepository = $this->dm->getRepository('MBHPackageBundle:Organization');
        $organizations = $organizationRepository->findBy($criteria, $sort,
            $request->get('length'), $request->get('start'));
        $recordsTotal = $organizationRepository->createQueryBuilder()->setQueryArray($criteria)->getQuery()->count();

        $response = $this->render('MBHPackageBundle:Organization:json.json.twig', [
            'organizations' => $organizations,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
        ]);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/create", name="create_organization")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_ORGANIZATION_NEW')")
     * @Template()
     */
    public function createAction(Request $request)
    {
        $organization = new Organization();
        //default value
        if(!$request->isMethod('PUT') && $request->get('type')) {
            $organization->setType($request->get('type'));
        }

        $form = $this->createForm(new OrganizationType($this->dm), $organization, [
            'typeList' => $this->container->getParameter('mbh.organization.types'),
        ]);

        if ($request->isMethod('PUT')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($organization);
                $this->dm->flush();

                $organization->upload();

                return $this->redirect($this->generateUrl('organizations', ['type' => $organization->getType()]));
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/{id}/edit", name="organization_edit")
     * @Method({"GET", "PUT"})
     * @Security("is_granted('ROLE_ORGANIZATION_EDIT')")
     * @ParamConverter("organization", class="MBHPackageBundle:Organization")
     * @Template()
     */
    public function editAction(Organization $organization, Request $request)
    {
        $imageUrl = $organization->getStamp() ? $this->generateUrl('stamp', ['id' => $organization->getId()]) : null;

        $form = $this->createForm(new OrganizationType($this->dm), $organization, [
            'typeList' => $this->container->getParameter('mbh.organization.types'),
            'id' => $organization->getId(),
            'type' => $organization->getType(),
            'scenario' => OrganizationType::SCENARIO_EDIT,
            'imageUrl' => $imageUrl
        ]);

        if ($request->isMethod('PUT')) {
            $form->submit($request);

            if ($form->isValid()) {
                $this->dm->persist($organization);
                $this->dm->flush();

                $imagine = new \Imagine\Gd\Imagine();
                $size = new \Imagine\Image\Box(400, 200);
                $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
                if($stamp = $organization->getStamp() and $stamp instanceof UploadedFile) {
                    $imagine->open($stamp->getPathname())->thumbnail($size, $mode)->save($stamp->getPathname(), [
                        'format' => $stamp->getClientOriginalExtension()
                    ]);

                    $organization->upload();
                }

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('organization_edit', ['id' => $organization->getId()]) :
                    $this->redirectToRoute('organizations');
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
     * @Security("is_granted('ROLE_ORGANIZATION_DELETE')")
     * @ParamConverter("organization", class="MBHPackageBundle:Organization")
     * @Template()
     */
    public function deleteAction(Organization $organization)
    {
        $this->dm->remove($organization);
        $this->dm->flush();

        return $this->redirect($this->generateUrl('organizations'));
    }


    /**
     * Get city by query
     *
     * @Route("/json/list", name="organization_json_list", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_ORGANIZATION_VIEW')")
     * @return JsonResponse
     */
    public function organizationJsonListAction(Request $request, $id = null)
    {
        if (empty($id) && empty($request->get('query'))) {
            return new JsonResponse([]);
        }

        if (!empty($id)) {
            $organization = $this->dm->getRepository('MBHPackageBundle:Organization')->find($id);

            if ($organization) {
                return new JsonResponse([
                    'id' => $organization->getId(),
                    'text' => $organization->getName()
                ]);
            }
        }

        $searchFields = [
            'name',
            'director_fio',
            'inn',
        ];

        $queryBuilder = $this->dm->getRepository('MBHPackageBundle:Organization')->createQueryBuilder('q')
            ->field('type')->equals('contragents') // criteria only contragents type
        ;

        $mongoRegex = new \MongoRegex('/.*'.$request->get('query').'.*/i');
        foreach ($searchFields as $fieldName) {
            $queryBuilder->addOr((new Expr())->field($fieldName)->equals($mongoRegex));
        }

        /** @var Organization[] $organizations */
        $organizations = $queryBuilder->limit(30)->getQuery()->execute();

        $data = [
            'list' => []
        ];

        foreach ($organizations as $organization) {
            $data['list'][] = [
                'id' => $organization->getId(),
                'text' => $organization->getName(),
            ];

            $data['details'][$organization->getId()] = [
                'name' => $organization->getName(),
                'fio' => $organization->getDirectorFio(),
                'phone' => $organization->getPhone(),
                'inn' => $organization->getInn(),
                'kpp' => $organization->getKpp(),
                'city' => $organization->getCity()->getId(),
                'city_name' => $organization->getCity(),
                'street' => $organization->getStreet(),
                'house' => $organization->getHouse(),
                'index' => $organization->getIndex(),
            ];
        }

        return new JsonResponse($data);
    }
}