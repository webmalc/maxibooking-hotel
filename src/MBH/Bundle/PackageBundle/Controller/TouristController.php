<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\ClientDataTableParams;
use MBH\Bundle\PackageBundle\Document\BirthPlace;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\Criteria\TouristQueryCriteria;
use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use MBH\Bundle\PackageBundle\Document\Migration;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Document\TouristRepository;
use MBH\Bundle\PackageBundle\Document\Unwelcome;
use MBH\Bundle\PackageBundle\Document\UnwelcomeRepository;
use MBH\Bundle\PackageBundle\Document\Visa;
use MBH\Bundle\PackageBundle\Form\AddressObjectDecomposedType;
use MBH\Bundle\PackageBundle\Form\DocumentRelationType;
use MBH\Bundle\PackageBundle\Form\TouristMigrationType;
use MBH\Bundle\PackageBundle\Form\TouristType;
use MBH\Bundle\PackageBundle\Form\TouristVisaType;
use MBH\Bundle\PackageBundle\Form\UnwelcomeType;
use MBH\Bundle\VegaBundle\Document\VegaFMS;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/tourist")
 */
class TouristController extends Controller
{
    /**
     * Lists all entities.
     *
     * @Route("/", name="tourist")
     * @Method("GET")
     * @Security("is_granted('ROLE_TOURIST_REPORT')")
     * @Template()
     */
    public function indexAction()
    {
        $form = $this->getTouristFilterForm();
        $vegaDocumentTypes = $this->container->get('mbh.vega.dictionary_provider')->getDocumentTypes();

        return [
            'form' => $form->createView(),
            'vegaDocumentTypes' => $vegaDocumentTypes
        ];
    }

    public function getTouristFilterForm()
    {
        $form = $this->createFormBuilder(null, [
            'data_class' => TouristQueryCriteria::class
        ])
            ->add('begin', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false
            ])
            ->add('end', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false
            ])
            ->add('citizenship',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'required' => false,
                'choices' => [
                    TouristQueryCriteria::CITIZENSHIP_NATIVE => 'Граждане РФ',
                    TouristQueryCriteria::CITIZENSHIP_FOREIGN => 'Иностранные граждане'
                ]
            ])
            ->add('search', TextType::class, [
                'required' => false
            ])
            ->getForm();

        return $form;
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="tourist_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("POST")
     * @Security("is_granted('ROLE_TOURIST_REPORT')")
     * @Template()
     */
    public function jsonAction(Request $request)
    {
        $tableParams = ClientDataTableParams::createFromRequest($request);
        $formData = (array)$request->get('form');
        $form = $this->getTouristFilterForm();
        $formData['search'] = $tableParams->getSearch();

        $form->submit($formData);
        if (!$form->isValid()) {
            return new JsonResponse(['error' => $form->getErrors()[0]->getMessage()]);
        }

        /** @var TouristQueryCriteria $criteria */
        $criteria = $form->getData();

        /** @var TouristRepository $touristRepository */
        $touristRepository = $this->dm->getRepository('MBHPackageBundle:Tourist');

        if($criteria->begin && $criteria->end) {
            $diff = $criteria->begin->diff($criteria->end);
            if($diff->y == 1 && $diff->m > 0 || $diff->y > 1) {
                $begin = clone($criteria->begin);
                $criteria->end = $begin->modify('+ 1 year');
            }
        }

        $tourists = $touristRepository->findByQueryCriteria($criteria, $tableParams->getStart(), $tableParams->getLength());

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        $packageCriteria = new PackageQueryCriteria();
        $packageCriteria->begin = $criteria->begin;
        $packageCriteria->end = $criteria->end;

        $touristPackages = [];
        foreach ($tourists as $tourist) {
            $touristPackages[$tourist->getId()] = $packageRepository->findOneByTourist($tourist, $packageCriteria);
        }

        $vegaDocumentTypes = $this->container->get('mbh.vega.dictionary_provider')->getDocumentTypes();
        $arrivals = $this->container->getParameter('mbh.package.arrivals');

        return [
            'tourists' => iterator_to_array($tourists),
            'total' => count($tourists),
            'draw' => $request->get('draw'),
            'touristPackages' => $touristPackages,
            'vegaDocumentTypes' => $vegaDocumentTypes,
            'arrivals' => $arrivals,
        ];
    }

    /**
     * Displays a form to create a new entity.
     *
     * @Route("/new", name="tourist_new")
     * @Method("GET")
     * @Security("is_granted('ROLE_TOURIST_NEW')")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Tourist();
        $entity->setCommunicationLanguage($this->container->getParameter('locale'));
        $form = $this->createForm(TouristType::class, $entity, [
            'genders' => $this->container->getParameter('mbh.gender.types')
        ]);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new entity via ajax.
     *
     * @Route("/create/ajax", name="tourist_create_ajax")
     * @Method("POST")
     * @Security("is_granted('ROLE_TOURIST_NEW')")
     */
    public function createAjaxAction(Request $request)
    {
        $entity = new Tourist();
        $entity->setDocumentRelation(new DocumentRelation());
        $entity->setBirthplace(new BirthPlace());
        $entity->setCitizenship($this->dm->getRepository('MBHVegaBundle:VegaState')->findOneByOriginalName('РОССИЯ'));
        $entity->getDocumentRelation()->setType('vega_russian_passport');

        $form = $this->createForm(TouristType::class, $entity,
            ['genders' => $this->container->getParameter('mbh.gender.types')]);
        $docForm = $this->createForm(DocumentRelationType::class, $entity);
        $addressForm = $this->createForm(AddressObjectDecomposedType::class, $entity->getAddressObjectDecomposed());

        $form->handleRequest($request);
        $docForm->handleRequest($request);
        $addressForm->handleRequest($request);

        if ($form->isValid() && $docForm->isValid() && $addressForm->isValid()) {

            $entity->setAddressObjectDecomposed($addressForm->getData());
            $this->dm->persist($entity);
            $this->dm->flush();

            $text = $entity->getFullName();

            if ($entity->getBirthday() || $entity->getPhone() || $entity->getEmail()) {
                $pieces = [];
                !$entity->getBirthday() ?: $pieces[] = $entity->getBirthday()->format('d.m.Y');
                !$entity->getPhone() ?: $pieces[] = $entity->getPhone();
                !$entity->getEmail() ?: $pieces[] = $entity->getEmail();
                $text .= ' (' . implode(', ', $pieces) . ')';
            }

            return new JsonResponse([
                'error' => false,
                'id' => $entity->getId(),
                'text' => $text
            ]);
        }
        $errors = [];
        foreach ($this->get('validator')->validate($entity) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse([
            'error' => true,
            'text' => implode('<br>', $errors)
        ]);
    }

    /**
     * Creates a new entity.
     *
     * @Route("/create", name="tourist_create")
     * @Method("POST")
     * @Security("is_granted('ROLE_TOURIST_NEW')")
     * @Template("MBHPackageBundle:Tourist:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $tourist = new Tourist();
        $form = $this->createForm(TouristType::class, $tourist,
            ['genders' => $this->container->getParameter('mbh.gender.types')]);

        $notUnwelcome = !$tourist->getIsUnwelcome();
        $form->handleRequest($request);
        if ($form->isValid()) {

            $this->dm->persist($tourist);
            $this->dm->flush();

            $flashBag = $request->getSession()->getFlashBag();
            if ($notUnwelcome && $tourist->getIsUnwelcome()) {
                $flashBag->set('warning', '<i class="fa fa-user-secret"></i> ' . $this->get('translator')
                        ->trans('controller.touristController.tourist_was_found_in_unwelcome'));
            }

            $flashBag->set('success', $this->get('translator')
                ->trans('controller.touristController.record_created_success'));

            return $this->afterSaveRedirect('tourist', $tourist->getId());
        }

        return [
            'entity' => $tourist,
            'form' => $form->createView(),
        ];
    }

    /**
     * Edits an existing entity.
     *
     * @Route("/{id}/edit", name="tourist_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template("MBHPackageBundle:Tourist:edit.html.twig")
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function updateAction(Request $request, Tourist $tourist)
    {
        $form = $this->createForm(TouristType::class, $tourist,
            ['genders' => $this->container->getParameter('mbh.gender.types')]);

        $notUnwelcome = !$tourist->getIsUnwelcome();
        $form->handleRequest($request);
        if ($form->isValid()) {

            $this->dm->persist($tourist);
            $this->dm->flush();

            $flashBag = $request->getSession()->getFlashBag();
            $flashBag->set('success', $this->get('translator')
                ->trans('controller.touristController.record_edited_success'));
            if ($notUnwelcome && $tourist->getIsUnwelcome()) {
                $flashBag->set('warning', '<i class="fa fa-user-secret"></i> ' . $this->get('translator')
                        ->trans('controller.touristController.tourist_was_found_in_unwelcome'));
            }

            return $this->afterSaveRedirect('tourist', $tourist->getId());
        }

        return [
            'entity' => $tourist,
            'form' => $form->createView(),
            'logs' => $this->logs($tourist)
        ];
    }

    /**
     * Displays a form to edit an existing entity.
     *
     * @Route("/{id}/edit", name="tourist_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editAction(Tourist $entity)
    {
        $form = $this->createForm(TouristType::class, $entity, [
            'genders' => $this->container->getParameter('mbh.gender.types')
        ]);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/{id}/edit/document", name="tourist_edit_document")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editDocumentAction(Tourist $entity, Request $request)
    {
        $entity->getBirthplace() ?: $entity->setBirthplace(new BirthPlace());
        $entity->getDocumentRelation() ?: $entity->setDocumentRelation(new DocumentRelation());

        //Default Value
        $entity->getCitizenship() ?: $entity->setCitizenship($this->dm->getRepository('MBHVegaBundle:VegaState')->findOneByOriginalName('РОССИЯ'));
        $entity->getDocumentRelation()->getType() ?: $entity->getDocumentRelation()->setType('vega_russian_passport');

        $form = $this->createForm(DocumentRelationType::class, $entity, [
            'method' => Request::METHOD_POST
        ]);

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.touristController.record_edited_success'));

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('tourist_edit_document', ['id' => $entity->getId()]) :
                    $this->redirectToRoute('tourist');
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     *
     * @Route("/{id}/edit/visa", name="tourist_edit_visa")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editVisaAction(Tourist $entity, Request $request)
    {
        $entity->getMigration() ?: $entity->setMigration(new Migration());
        $entity->getVisa() ?: $entity->setVisa(new Visa());
        $entity->getVisa()->getType() ?: $entity->getVisa()->setType('visa');

        $form = $this->createFormBuilder($entity)
            ->add('visa', new TouristVisaType())
            ->add('migration', new TouristMigrationType())
            ->getForm();

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.touristController.record_edited_success'));

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('tourist_edit_visa', ['id' => $entity->getId()]) :
                    $this->redirectToRoute('tourist');
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/{id}/edit/unwelcome", name="tourist_edit_unwelcome")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editUnwelcomeAction(Tourist $tourist, Request $request)
    {
        $form = $this->createForm(UnwelcomeType::class, null, [
            'method' => Request::METHOD_POST
        ]);

        /** @var UnwelcomeRepository $unwelcomeRepository */
        $unwelcomeRepository = $this->get('mbh.package.unwelcome_repository');
        /** @var Unwelcome[] $unwelcomeList */
        $unwelcomeList = $unwelcomeRepository->findByTourist($tourist);
        if ($unwelcomeList) {
            foreach ($unwelcomeList as $unwelcome) {
                if ($unwelcome->getIsMy()) {
                    $form->setData($unwelcome);
                } else {
                    $unwelcomeList[] = $unwelcome;
                }
            }
        }

        if (!$form->getData()) {
            $unwelcome = new Unwelcome();
            $unwelcome
                ->setAggression(0)
                ->setFoul(0)
                ->setInadequacy(0)
                ->setDrunk(0)
                ->setDrugs(0)
                ->setDestruction(0)
                ->setMaterialDamage(0)
                ->setIsMy(false);

            $form->setData($unwelcome);
        }

        $isTouristValid = $unwelcomeRepository->isInsertedTouristValid($tourist);
        if ($isTouristValid) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $unwelcome = $form->getData();
                if ($unwelcome->getIsMy()) {
                    $unwelcomeRepository->update($unwelcome, $tourist);
                } else {
                    $package = $this->dm->getRepository('MBHPackageBundle:Package')->findOneByTourist($tourist);
                    if ($package) {
                        $unwelcome->setArrivalTime($package->getArrivalTime());
                        $unwelcome->setDepartureTime($package->getDepartureTime());
                    }
                    $unwelcomeRepository->add($unwelcome, $tourist, $package);
                }
                $tourist->setIsUnwelcome(true);
                $this->dm->persist($tourist);
                $this->dm->flush($tourist);

                return $this->redirectToRoute('tourist_edit_unwelcome', ['id' => $tourist->getId()]);
            }
        }

        return [
            'isTouristValid' => $isTouristValid,
            'form' => $form->createView(),
            'unwelcome' => $form->getData(),
            'unwelcomeList' => $unwelcomeList,
            'tourist' => $tourist,
            'characteristics' => UnwelcomeType::getCharacteristics(),
            'logs' => $this->logs($tourist),
        ];
    }

    /**
     * @Route("/{id}/delete/unwelcome", name="tourist_delete_unwelcome")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function deleteUnwelcomeAction(Tourist $tourist)
    {
        /** @var UnwelcomeRepository $unwelcomeRepository */
        $unwelcomeRepository = $this->get('mbh.package.unwelcome_repository');
        $unwelcomeRepository->deleteByTourist($tourist);

        $tourist->setIsUnwelcome($unwelcomeRepository->isUnwelcome($tourist));
        $this->dm->persist($tourist);
        $this->dm->flush($tourist);

        return $this->redirectToRoute('tourist_edit_unwelcome', ['id' => $tourist->getId()]);
    }


    /**
     * @Route("/regions", name="get_json_regions", options={"expose"=true})
     * @Method("GET")
     */
    public function ajaxRegionAction(Request $request)
    {
        $list = [];
        $criteria = [];
        if ($value = $request->get('value')) {
            $criteria = ['title' => new \MongoRegex('/.*' . $value . '.*/ui')];
        }

        $entities = $this->dm->getRepository('MBHHotelBundle:Region')->findBy($criteria, ['title' => 1], 50);
        foreach ($entities as $entity) {
            $list[$entity->getId()] = $entity->getTitle();
        }

        return new JsonResponse(['data' => $list]);
    }

    /**
     * @Route("/authority_organ_json_list", name="authority_organ_json_list", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_BASE_USER')")
     */
    public function authorityOrganListAction(Request $request)
    {
        $list = [];
        if ($query = $request->get('query')['term']) {
            $repository = $this->dm->getRepository('MBHVegaBundle:VegaFMS');
            $entities = $repository->findBy(['name' => new \MongoRegex('/.*' . $query . '.*/ui')], ['name' => 1], 50);
            foreach ($entities as $entity) {

                $list[] = [
                    'id' => $entity->getId(),
                    'text' => $entity->getName()
                ];

                //$list[$entity->getId()] = $entity->getName();
            }
        }

        return new JsonResponse(['results' => $list]);
    }

    /**
     * @Route("/authority_organ/{id}", name="ajax_authority_organ", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @ParamConverter("vegaFMS", class="MBHVegaBundle:VegaFMS")
     */
    public function authorityOrganAction(VegaFMS $vegaFMS)
    {
        return new JsonResponse([
            'id' => $vegaFMS->getId(),
            'text' => $vegaFMS->getName(),
            'code' => $vegaFMS->getCode()
        ]);
    }

    /**
     * @Route("/{id}/edit/address", name="tourist_edit_address")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     */
    public function editAddressAction(Tourist $entity, Request $request)
    {
        $form = $this->createForm(AddressObjectDecomposedType::class, $entity->getAddressObjectDecomposed());

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $entity->setAddressObjectDecomposed($form->getData());
                $this->dm->persist($entity);
                $this->dm->flush();

                $request->getSession()->getFlashBag()->set('success',
                    $this->get('translator')->trans('controller.touristController.record_edited_success'));

                return $this->isSavedRequest() ?
                    $this->redirectToRoute('tourist_edit_address', ['id' => $entity->getId()]) :
                    $this->redirectToRoute('tourist');
            }
        }

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * Delete entity.
     *
     * @Route("/{id}/delete", name="tourist_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_TOURIST_DELETE')")
     */
    public function deleteAction($id)
    {
        return $this->deleteEntity($id, 'MBHPackageBundle:Tourist', 'tourist');
    }

    /**
     * Get city by query
     *
     * @Route("/get/{id}/json", name="json_tourist", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_TOURIST_VIEW')")
     * @ParamConverter("tourist", class="MBHPackageBundle:Tourist")
     * @return JsonResponse
     */
    public function jsonEntryAction(Tourist $tourist)
    {
        return new JsonResponse($tourist);
    }

    /**
     * Get city by query
     *
     * @Route("/get/{id}", name="ajax_tourists", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_TOURIST_VIEW')")
     * @return JsonResponse
     */
    public function ajaxListAction(Request $request, $id = null)
    {
        if (empty($id) && empty($request->get('query'))) {
            return new JsonResponse([]);
        }

        if (!empty($id)) {
            $payer = $this->dm->getRepository('MBHPackageBundle:Tourist')->find($id);
            if ($payer) {
                $text = $payer->getFullName();

                if ($payer->getBirthday() || $payer->getPhone() || $payer->getEmail()) {
                    $pieces = [];
                    !$payer->getBirthday() ?: $pieces[] = $payer->getBirthday()->format('d.m.Y');
                    !$payer->getPhone() ?: $pieces[] = $payer->getPhone();
                    !$payer->getEmail() ?: $pieces[] = $payer->getEmail();
                    $text .= ' (' . implode(', ', $pieces) . ')';
                }

                return new JsonResponse([
                    'id' => $payer->getId(),
                    'text' => $text
                ]);
            }
        }

        $regex = new \MongoRegex('/.*' . $request->get('query') . '.*/i');
        $qb = $this->dm->getRepository('MBHPackageBundle:Tourist')->createQueryBuilder('q')
            ->sort(['fullName' => 'asc', 'birthday' => 'desc'])->limit(100);
        $qb->addOr($qb->expr()->field('fullName')->equals($regex));
        $qb->addOr($qb->expr()->field('email')->equals($regex));
        $qb->addOr($qb->expr()->field('phone')->equals($regex));

        $payers = $qb->getQuery()->execute();

        $data = [];

        foreach ($payers as $payer) {
            $text = $payer->getFullName();
            if ($payer->getBirthday() || $payer->getPhone() || $payer->getEmail()) {
                $pieces = [];
                !$payer->getBirthday() ?: $pieces[] = $payer->getBirthday()->format('d.m.Y');
                !$payer->getPhone() ?: $pieces[] = $payer->getPhone();
                !$payer->getEmail() ?: $pieces[] = $payer->getEmail();
                $text .= ' (' . implode(', ', $pieces) . ')';
            }

            $data[] = [
                'id' => $payer->getId(),
                'text' => $text
            ];
        }

        return new JsonResponse(['results' => $data]);
    }
}