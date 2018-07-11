<?php

namespace MBH\Bundle\PackageBundle\Controller;

use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\ClientBundle\Lib\FMSDictionaries;
use MBH\Bundle\PackageBundle\Document\BirthPlace;
use MBH\Bundle\PackageBundle\Document\Criteria\PackageQueryCriteria;
use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use MBH\Bundle\PackageBundle\Document\Migration;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Document\Unwelcome;
use MBH\Bundle\PackageBundle\Document\UnwelcomeRepository;
use MBH\Bundle\PackageBundle\Document\Visa;
use MBH\Bundle\PackageBundle\Form\AddressObjectDecomposedType;
use MBH\Bundle\PackageBundle\Form\DocumentRelationType;
use MBH\Bundle\PackageBundle\Form\TouristFilterForm;
use MBH\Bundle\PackageBundle\Form\TouristMigrationType;
use MBH\Bundle\PackageBundle\Form\TouristType;
use MBH\Bundle\PackageBundle\Form\TouristVisaType;
use MBH\Bundle\PackageBundle\Form\UnwelcomeType;
use MBH\Bundle\BillingBundle\Lib\Model\Country;
use MBH\Bundle\VegaBundle\Document\VegaState;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $form = $this->createForm(TouristFilterForm::class);
        $hasMyOrganization = !empty($this->dm->getRepository('MBHPackageBundle:Organization')->getForFmsExport());

        return [
            'form' => $form->createView(),
            'hasMyOrganization' => $hasMyOrganization
        ];
    }

    /**
     * Lists all entities as json.
     *
     * @Route("/json", name="tourist_json", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("POST")
     * @Security("is_granted('ROLE_TOURIST_REPORT')")
     * @Template()
     * @param Request $request
     * @return array|JsonResponse
     */
    public function jsonAction(Request $request)
    {
        $qbData = $this->get('mbh.tourist_manager')->getQueryBuilderByRequestData($request, $this->getUser(), $this->hotel);

        if (!$qbData instanceof Builder) {
            return new JsonResponse(['error' => $qbData]);
        }
        $tourists = $qbData->getQuery()->execute();

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');

        $packageCriteria = new PackageQueryCriteria();
        $formData = $request->request->get('form');
        $packageCriteria->begin = $this->helper->getDateFromString($formData['begin']);
        $packageCriteria->end = $this->helper->getDateFromString($formData['end']);

        $touristPackages = [];
        foreach ($tourists as $tourist) {
            $touristPackages[$tourist->getId()] = $packageRepository->findOneByTourist($tourist, $packageCriteria);
        }

        $arrivals = $this->container->getParameter('mbh.package.arrivals');

        return [
            'tourists' => iterator_to_array($tourists),
            'total' => count($tourists),
            'draw' => $request->get('draw'),
            'touristPackages' => $touristPackages,
            'documentTypes' => $this->get('mbh.fms_dictionaries')->getDocumentTypes(),
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
     * @param Request $request
     * @return JsonResponse
     */
    public function createAjaxAction(Request $request)
    {
        $entity = new Tourist();
        $entity->setDocumentRelation(new DocumentRelation());
        $entity->setBirthplace(new BirthPlace());
        $entity->getDocumentRelation()->setType(FMSDictionaries::RUSSIAN_PASSPORT_ID);

        $form = $this->createForm(
            TouristType::class,
            $entity,
            ['genders' => $this->container->getParameter('mbh.gender.types')]
        );
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
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $tourist = new Tourist();
        $form = $this->createForm(
            TouristType::class,
            $tourist,
            ['genders' => $this->container->getParameter('mbh.gender.types')]
        );

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
     * @param Request $request
     * @param Tourist $tourist
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, Tourist $tourist)
    {
        $form = $this->createForm(
            TouristType::class,
            $tourist,
            ['genders' => $this->container->getParameter('mbh.gender.types')]
        );

        $notUnwelcome = !$tourist->getIsUnwelcome();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dm->persist($tourist);
            $this->dm->flush();

            $this->addFlash('success', 'controller.touristController.record_edited_success');
            if ($notUnwelcome && $tourist->getIsUnwelcome()) {
                $this->addFlash('warning', '<i class="fa fa-user-secret"></i> ' . $this->get('translator')
                        ->trans('controller.touristController.tourist_was_found_in_unwelcome'));
            }

            $redirectPath = $request->request->get('redirectTo');

            return $this->afterSaveRedirectExtended('tourist', $tourist->getId(), [], '_edit', $redirectPath);
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
     * @param Tourist $entity
     * @param Request $request
     * @return array
     */
    public function editAction(Tourist $entity, Request $request)
    {
        $form = $this->createForm(TouristType::class, $entity, [
            'genders' => $this->container->getParameter('mbh.gender.types')
        ]);

        return [
            'entity' => $entity,
            'form' => $form->createView(),
            'logs' => $this->logs($entity),
            'redirectTo' => $request->query->get('redirectTo')
        ];
    }

    /**
     * @Route("/{id}/edit/document", name="tourist_edit_document")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     * @param Tourist $entity
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editDocumentAction(Tourist $entity, Request $request)
    {
        $entity->getBirthplace() ?: $entity->setBirthplace(new BirthPlace());
        $entity->getDocumentRelation() ?: $entity->setDocumentRelation(new DocumentRelation());

        //Default Value
        $entity->getCitizenshipTld() ?: $entity->setCitizenshipTld($this->get('mbh.client_manager')->getClient()->getCountry());
        $entity->getDocumentRelation()->getType() ?: $entity->getDocumentRelation()->setType(FMSDictionaries::RUSSIAN_PASSPORT_ID);

        $form = $this->createForm(DocumentRelationType::class, $entity, [
            'method' => Request::METHOD_POST,
        ]);

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $this->addFlash('success', 'controller.touristController.record_edited_success');

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
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Method({"POST"})
     * @Route("/add_new_country", name="new_vega_state", options={"expose": true})
     * @param Request $request
     * @return JsonResponse
     */
    public function addNewCountryAction(Request $request)
    {
        $isSuccess = true;
        $countryName = $request->request->get('countryName');
        $country = (new VegaState())
            ->setName($countryName)
            ->setOriginalName($countryName);

        $errors = $this->get('validator')->validate($country);
        if (count($errors) > 0) {
            $isSuccess = false;
            $errorsList = [];
            foreach ($errors as $error) {
                $errorsList[] = $error->getMessage();
            }

            return new JsonResponse([
                'success' => $isSuccess,
                'errors' => $errorsList
            ]);
        } else {
            $this->dm->persist($country);
            $this->dm->flush();
        }

        return new JsonResponse([
            'success' => $isSuccess,
            'country' => ['id' => $country->getId(), 'name' => $country->getName()]
        ]);
    }

    /**
     * @Route("/{id}/edit/visa", name="tourist_edit_visa")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     * @param Tourist $entity
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editVisaAction(Tourist $entity, Request $request)
    {
        $entity->getMigration() ?: $entity->setMigration(new Migration());
        $entity->getVisa() ?: $entity->setVisa(new Visa());
        $entity->getVisa()->getType() ?: $entity->getVisa()->setType('visa');

        $form = $this->createFormBuilder($entity)
            ->add('visa', TouristVisaType::class)
            ->add('migration', TouristMigrationType::class)
            ->getForm();

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->dm->persist($entity);
                $this->dm->flush();

                $this->addFlash('success', 'controller.touristController.record_edited_success');

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
     * @param Tourist $tourist
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
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
//                    $unwelcomeRepository->add($unwelcome, $tourist, $package);
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
     * @param Tourist $tourist
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
     * @param Request $request
     * @return JsonResponse
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
     * @Route("/{id}/edit/address", name="tourist_edit_address")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_TOURIST_EDIT')")
     * @Template()
     * @ParamConverter("entity", class="MBHPackageBundle:Tourist")
     * @param Tourist $entity
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
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

                $this->addFlash('success', 'controller.touristController.record_edited_success');

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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
     * @param Tourist $tourist
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
     * @param Request $request
     * @param null $id
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
        $qb = $this->dm->getRepository('MBHPackageBundle:Tourist')->createQueryBuilder()
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

    /**
     * @Route("/export_to_fms_system/{system}", name="export_to_fms_system", options={"expose"=true})
     * @param Request $request
     * @param string $system
     * @return Response
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     * @Security("is_granted('ROLE_TOURIST_REPORT')")
     */
    public function compileFMSArchive(Request $request, string $system)
    {
        $touristQB = $this->get('mbh.tourist_manager')
            ->getQueryBuilderByRequestData($request, $this->getUser(), $this->get('mbh.hotel.selector')->getSelected())
            ->limit(0);

        $packageCriteria = new PackageQueryCriteria();
        $formData = $request->query->get('form');
        $beginDate = $this->helper->getDateFromString($formData['begin']);
        $endDate = $this->helper->getDateFromString($formData['end']);

        $packageCriteria->begin = $beginDate;
        $packageCriteria->end = $endDate;

        $stringsToWriteByNames = [];
        /** @var Tourist $tourist */
        $packageRepository = $this->dm->getRepository('MBHPackageBundle:Package');
        $mainOrganization = $this->dm->getRepository('MBHPackageBundle:Organization')->findOneBy(['type' => 'my']);
        foreach ($touristQB->getQuery()->execute() as $tourist) {
            $touristPackage = $packageRepository->findOneByTourist($tourist, $packageCriteria);
            if (!is_null($touristPackage) && $tourist->getLastName() != 'Н/д') {
                $isRussianTourist = $tourist->getCitizenshipTld() === Country::RUSSIA_TLD;
                if ($system === 'kontur') {
                    $viewFile = $isRussianTourist
                        ? '@MBHClient/Fms/fms_export_russian.xml.twig'
                        : '@MBHClient/Fms/fms_export_foreign.xml.twig';
                } elseif($system === 'sbis') {
                    $viewFile = $isRussianTourist
                        ? '@MBHClient/Fms/export_to_sbis.xml.twig'
                        : '@MBHClient/Fms/export_to_sbis_foreign.xml.twig';
                } else {
                    throw new \InvalidArgumentException('Incorrect export system name "' . $system . '"');
                }

                $xml = $this->renderView($viewFile, [
                    'package' => $touristPackage,
                    'tourist' => $tourist,
                    'organization' => $mainOrganization
                ]);

                $stringsToWriteByNames[$tourist->getName() . '.xml'] =  $xml;
            }
        }

        $zipManager = $this->get('mbh.zip_manager');
        $zipFileName = $system . '-export ' . $this->helper->getDatePeriodString($beginDate, $endDate, 'Y.m.d') . '.zip';

        return $zipManager->writeToStreamedResponse($stringsToWriteByNames, $zipFileName);
    }
}
