<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\PackageBundle\Document\BirthPlace;
use MBH\Bundle\PackageBundle\Document\DocumentRelation;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Form\AddressObjectDecomposedType;
use MBH\Bundle\PackageBundle\Form\ChessBoardConciseType;
use MBH\Bundle\PackageBundle\Form\DocumentRelationType;
use MBH\Bundle\PackageBundle\Form\TouristType;
use MBH\Bundle\PackageBundle\Services\ChessBoardMessageFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use MBH\Bundle\PackageBundle\Form\SearchType;

/**
 * @Route("/chessboard")
 */
class ChessBoardController extends BaseController
{
    const SIZE_CONFIGS = [
        [
            'headerWidth' => 180,
            'tableCellHeight' => 25,
            'tableCellWidth' => 33,
            'tileFontSize' => 10,
            'tileTopPadding' => 5,
            'dayTopPadding' => 7,
            'titleSubPadding' => 0,
            'titleSubFontSize' => 11,
            'leftRoomsAndNoAccFontSize' => 16,
        ],
        [
            'headerWidth' => 180,
            'tableCellHeight' => 30,
            'tableCellWidth' => 40,
            'tileFontSize' => 10,
            'tileTopPadding' => 7,
            'dayTopPadding' => 0,
            'titleSubPadding' => 0,
            'titleSubFontSize' => 11,
            'leftRoomsAndNoAccFontSize' => 16,
        ],
        [
            'headerWidth' => 200,
            'tableCellHeight' => 40,
            'tableCellWidth' => 47,
            'tileFontSize' => 12,
            'tileTopPadding' => 12,
            'dayTopPadding' => 5,
            'titleSubPadding' => 5,
            'titleSubFontSize' => 11,
            'leftRoomsAndNoAccFontSize' => 16,
        ],
        [
            'headerWidth' => 200,
            'tableCellHeight' => 47,
            'tableCellWidth' => 55,
            'tileFontSize' => 14,
            'tileTopPadding' => 12,
            'dayTopPadding' => 9,
            'titleSubPadding' => 0,
            'titleSubFontSize' => 11,
            'leftRoomsAndNoAccFontSize' => 20,
        ]
    ];

    /**
     * @Route("/", name="chess_board_home", options={"expose"=true})
     * @Template()
     * @param Request $request
     * @Security("is_granted('ROLE_PACKAGE_VIEW')")
     * @return array
     */
    public function indexAction(Request $request)
    {
        $filterData = $this->getFilterData($request);

        $builder = $this->get('mbh.package.report_data_builder')
            ->init($this->hotel, $filterData['begin'], $filterData['end'],
                $filterData['roomTypeIds'], $filterData['housing'], $filterData['floor'], null,
                $filterData['pageNumber']);

        $form = $this->createForm(SearchType::class, null, [
            'security' => $this->container->get('mbh.hotel.selector'),
            'dm' => $this->dm,
            'hotel' => $this->hotel,
            'roomManager' => $this->get('mbh.hotel.room_type_manager')
        ]);

        $stylesFileNumber = $request->cookies->get('chessboardSizeNumber') ?? 1;

        $rightsChecker = $this->get('security.authorization_checker');
        $canCreatePackage = $rightsChecker->isGranted('ROLE_PACKAGE_NEW') && $rightsChecker->isGranted('ROLE_SEARCH') ? 'true' : 'false';
        $clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        $displayDisabledRoomType = !$clientConfig->isIsDisableableOn();
        $canBookWithoutPayer = $clientConfig->isCanBookWithoutPayer();

        $tourist = new Tourist();
        $tourist->setDocumentRelation(new DocumentRelation());
        $tourist->setBirthplace(new BirthPlace());
        $tourist->setCitizenship($this->dm->getRepository('MBHVegaBundle:VegaState')->findOneByOriginalName('РОССИЯ'));
        $tourist->getDocumentRelation()->setType('vega_russian_passport');

        return [
            'pageCount' => ceil($builder->getRoomCount() / $builder::ROOM_COUNT_ON_PAGE),
            'pageNumber' => $filterData['pageNumber'],
            'searchForm' => $form->createView(),
            'beginDate' => $filterData['begin'],
            'endDate' => $filterData['end'],
            'calendarData' => $builder->getCalendarData(),
            'days' => $builder->getDaysArray(),
            'roomTypesData' => $builder->getRoomTypeData(),
            'leftRoomsData' => $builder->getLeftRoomCounts(),
            'roomStatusIcons' => $this->getParameter('mbh.room_status_icons'),
            'packages' => json_encode($builder->getAccommodationIntervals()),
            'noAccommodationIntervals' => json_encode($builder->getNoAccommodationPackageIntervals()),
            'leftRoomsJsonData' => json_encode($builder->getLeftRoomCounts()),
            'noAccommodationCounts' => json_encode($builder->getDayNoAccommodationPackageCounts()),
            'roomTypes' => $builder->getAvailableRoomTypes(),
            'housings' => $this->hotel->getHousings(),
            'floors' => $this->dm->getRepository('MBHHotelBundle:Room')->fetchFloors(),
            'canCreatePackage' => $canCreatePackage,
            'canBookWithoutPayer' => $canBookWithoutPayer ? 'true' : 'false',
            'displayDisabledRoomType' => $displayDisabledRoomType,
            'touristForm' => $this->createForm(TouristType::class, null,
                ['genders' => $this->container->getParameter('mbh.gender.types')])->createView(),
            'documentForm' => $this->createForm(DocumentRelationType::class, $tourist)
                ->createView(),
            'addressForm' => $this->createForm(AddressObjectDecomposedType::class,
                $tourist->getAddressObjectDecomposed())
                ->createView(),
            'sizes' => self::SIZE_CONFIGS,
            'stylesFileNumber' => $stylesFileNumber
        ];
    }

    /**
     * @Method({"GET"})
     * @Route("/packages/{id}", name="chessboard_get_package", options={"expose"=true})
     * @param Package $package
     * @Security("is_granted('ROLE_PACKAGE_VIEW_ALL') or (is_granted('VIEW', package) and is_granted('ROLE_PACKAGE_VIEW'))")
     * @return array
     * @Template()
     */
    public function getPackageAction(Package $package)
    {
        return [
            'package' => $package
        ];
    }

    /**
     * @Method({"DELETE"})
     * @Route("/packages/{id}", name="chessboard_remove_package", options={"expose"=true})
     * @param Package $package
     * @Security("is_granted('ROLE_PACKAGE_DELETE') and (is_granted('DELETE', package) or is_granted('ROLE_PACKAGE_DELETE_ALL'))")
     * @return JsonResponse
     */
    public function removePackageAction(Package $package)
    {
        $messageFormatter = $this->get('mbh.chess_board.message_formatter');
        if (!$this->container->get('mbh.package.permissions')->checkHotel($package)) {
            throw $this->createNotFoundException();
        }
        try {
            $this->dm->remove($package);
            $this->dm->flush();
        } catch (\Exception $e) {
            $message = $this->get('translator')->trans($e->getMessage());
            $messageFormatter->addErrorMessage($e->getMessage());
            $this->logs($message);
        }
        $messageFormatter->addSuccessfulMessage('controller.chessboard.package_remove.success');

        return new JsonResponse(json_encode($messageFormatter->getMessages()));
    }

    /**
     * @Method({"PUT"})
     * @Route("/packages/{id}", name="concise_package_update", options={"expose"=true})
     * @param Request $request
     * @param Package $package
     * @return JsonResponse
     * @throws \Exception
     */
    public function updatePackageAction(Request $request, Package $package)
    {
        $helper = $this->container->get('mbh.helper');
        $messageFormatter = $this->container->get('mbh.chess_board.message_formatter');

        $updatedBeginDate = $helper::getDateFromString($request->request->get('begin'));
        $updatedEndDate = $helper::getDateFromString($request->request->get('end'));
        $accommodationId = $request->request->get('accommodationId');
        $updatedRoom = $this->dm->find('MBHHotelBundle:Room', $request->request->get('roomId'));

        //Если изменяется размещение, а не добавляется новое
        if ($accommodationId != '') {
            $accommodation = $this->dm->find('MBHPackageBundle:PackageAccommodation', $accommodationId);
            //Если удаляется размещение
            if (!$updatedRoom) {
                $this->dm->remove($accommodation);
                $this->dm->flush();
                $messageFormatter->addSuccessRemoveAccommodationMessage($accommodation);
            } else {
                $this->updateAccommodation($package, $accommodation, $updatedRoom, $updatedBeginDate,
                    $updatedEndDate, $messageFormatter);
            }
        } else {
            $this->addAccommodation($updatedBeginDate, $updatedEndDate, $updatedRoom, $package, $messageFormatter);
        }

        return new JsonResponse(json_encode($messageFormatter->getMessages()));
    }

    /**
     * @param Package $package
     * @param PackageAccommodation $accommodation
     * @param Room $updatedRoom
     * @param \DateTime $updatedBeginDate
     * @param \DateTime $updatedEndDate
     * @param ChessBoardMessageFormatter $messageFormatter
     * @throws \Exception
     */
    private function updateAccommodation(
        Package $package,
        PackageAccommodation $accommodation,
        Room $updatedRoom,
        \DateTime $updatedBeginDate,
        \DateTime $updatedEndDate,
        ChessBoardMessageFormatter $messageFormatter
    ) {
        $oldPackage = clone $package;
        $accommodation->setAccommodation($updatedRoom);

        $isLastAccommodation = $accommodation == $oldPackage->getLastAccommodation();
        $isFirstAccommodation = $accommodation == $oldPackage->getFirstAccommodation();
        $isBeginDateChanged = $updatedBeginDate->format('d.m.Y') != $accommodation->getBegin()->format('d.m.Y');
        $isEndDateChanged = $updatedEndDate->format('d.m.Y') != $accommodation->getEnd()->format('d.m.Y');

        //Если изменилась дата или конец размещения, но это не первое и не последнее размещение
        if (($isBeginDateChanged && !$isFirstAccommodation) || ($isEndDateChanged && !$isLastAccommodation)) {
            throw new \Exception($this->get('translator')
                ->trans('controller.chessboard.accommodation_update.not_first_or_last_accommodation_change'));
        }

        $isPackageChanged = false;
        if ($isBeginDateChanged && $isFirstAccommodation) {
            $package->setBegin($updatedBeginDate);
            $isPackageChanged = true;
        }
        if ($isEndDateChanged && $isLastAccommodation
            //Если дата окончания размещения больше чем дата выезда брони
            && (($updatedEndDate->getTimestamp() > $package->getEnd()->getTimestamp())
                //... или дата выезда брони равна дате окончания размещения, то изменяем дату выезда брони
                || ($package->getEnd()->getTimestamp() == $accommodation->getEnd()->getTimestamp()))
        ) {
            $package->setEnd(clone $updatedEndDate);
            $isPackageChanged = true;
        }

        if ($isPackageChanged) {
            $this->updatePackageWithAccommodation($oldPackage, $package, $accommodation, $updatedBeginDate,
                $updatedEndDate, $messageFormatter);
        } else {
            $rightsChecker = $this->get('security.authorization_checker');
            if (!($rightsChecker->isGranted('ROLE_PACKAGE_ACCOMMODATION')
                && ($rightsChecker->isGranted('ROLE_PACKAGE_EDIT_ALL')
                    || $rightsChecker->isGranted('EDIT', $accommodation))
            )
            ) {
                throw $this->createAccessDeniedException();
            }
            $editResult = $this->get('mbh_bundle_package.services.package_accommodation_manipulator')
                ->editAccommodation($accommodation, $updatedBeginDate, $updatedEndDate);
            if ($editResult instanceof PackageAccommodation) {
                $messageFormatter->addSuccessUpdateAccommodationMessage();
            } else {
                $messageFormatter->addErrorMessage($editResult);
            }
        }
    }

    /**
     * @param \DateTime $updatedBeginDate
     * @param \DateTime $updatedEndDate
     * @param Room $updatedRoom
     * @param Package $package
     * @param ChessBoardMessageFormatter $messageFormatter
     */
    private function addAccommodation(
        \DateTime $updatedBeginDate,
        \DateTime $updatedEndDate,
        Room $updatedRoom,
        Package $package,
        ChessBoardMessageFormatter &$messageFormatter
    ) {
        $rightsChecker = $this->get('security.authorization_checker');
        if (!($rightsChecker->isGranted('ROLE_PACKAGE_VIEW_ALL')
            || ($rightsChecker->isGranted('ROLE_PACKAGE_EDIT_ALL')
                && $rightsChecker->isGranted('VIEW', $package))
        )
        ) {
            throw $this->createAccessDeniedException();
        }

        $accommodation = new PackageAccommodation();
        $accommodation->setBegin($updatedBeginDate);
        $accommodation->setEnd($updatedEndDate);
        $accommodation->setRoom($updatedRoom);
        $additionResult = $this->get('mbh_bundle_package.services.package_accommodation_manipulator')
            ->addAccommodation($accommodation, $package);
        if ($additionResult instanceof PackageAccommodation) {
            $messageFormatter->addSuccessAddAccommodationMessage($accommodation, $package);
        } else {
            $messageFormatter->addErrorMessage($additionResult);
        }
    }

    private function updatePackageWithAccommodation(
        Package $oldPackage,
        Package $newPackage,
        PackageAccommodation $accommodation,
        \DateTime $updatedBeginDate,
        \DateTime $updatedEndDate,
        ChessBoardMessageFormatter $messageFormatter
    ) {
        $rightsChecker = $this->get('security.authorization_checker');
        if (!($rightsChecker->isGranted('ROLE_PACKAGE_EDIT') && ($rightsChecker->isGranted('ROLE_PACKAGE_EDIT_ALL')
                || $rightsChecker->isGranted('EDIT', $oldPackage))
        )
        ) {
            throw $this->createAccessDeniedException();
        }

        $packageValidateErrors = $this->get('validator')->validate($newPackage);
        if (count($packageValidateErrors) === 0) {
            $result = $this->container->get('mbh.order_manager')->updatePackage($oldPackage, $newPackage);
            if ($result instanceof Package) {
                $this->dm->persist($newPackage);
                $this->dm->flush();
                $editResult = $this->get('mbh_bundle_package.services.package_accommodation_manipulator')
                    ->editAccommodation($accommodation, $updatedBeginDate, $updatedEndDate);
                if ($editResult instanceof PackageAccommodation) {
                    $messageFormatter->addSuccessPackageUpdateMessage();
                } else {
                    $messageFormatter->addErrorMessage($editResult);
                }
            } else {
                $messageFormatter->addErrorMessage($result);
            }
        } else {
            foreach ($packageValidateErrors as $error) {
                $messageFormatter->addErrorMessage($error->getMessage());
            }
        }
    }

    /**
     * @Route("/accommodation_relocate/{id}", name="relocate_accommodation", options={"expose"=true})
     * @param Request $request
     * @param PackageAccommodation $firstAccommodation
     * @Security("is_granted('ROLE_PACKAGE_ACCOMMODATION') and (is_granted('EDIT', firstAccommodation) or is_granted('ROLE_PACKAGE_EDIT_ALL'))")
     * @return JsonResponse
     * @throws \Exception
     */
    public function relocateAccommodation(Request $request, PackageAccommodation $firstAccommodation)
    {
        $messageFormatter = $this->get('mbh.chess_board.message_formatter');
        $translator = $this->get('translator');
        $helper = $this->container->get('mbh.helper');

        $package = $firstAccommodation->getPackage();
        $accommodationEnd = $firstAccommodation->getEnd();

        $intermediateDateString = $request->request->get('begin');
        $roomId = $request->request->get('roomId');
        if ($intermediateDateString == '') {
            throw new \Exception($translator->trans('controller.chessboard.accommodation_divide.error.empty_date_string'));
        }
        $intermediateDate = $helper::getDateFromString($intermediateDateString);

        if ($intermediateDate->getTimestamp() < $firstAccommodation->getEnd()->getTimestamp()
            && $intermediateDate->getTimestamp() > $firstAccommodation->getBegin()->getTimestamp()
        ) {
            $editResult = $this->get('mbh_bundle_package.services.package_accommodation_manipulator')
                ->editAccommodation($firstAccommodation, null, $intermediateDate);

            if (!($editResult instanceof PackageAccommodation)) {
                $messageFormatter->addErrorMessage($editResult);
            } else {
                $secondAccommodation = clone $firstAccommodation;
                $secondAccommodation->setBegin($intermediateDate);
                $secondAccommodation->setEnd($accommodationEnd);

                if ($roomId != '') {
                    $room = $this->dm->find('MBHHotelBundle:Room', $roomId);
                    $secondAccommodation->setRoom($room);
                    $additionResult = $this->get('mbh_bundle_package.services.package_accommodation_manipulator')
                        ->addAccommodation($secondAccommodation, $package);
                    if ($additionResult instanceof PackageAccommodation) {
                        $messageFormatter->addSuccessAddAccommodationMessage($secondAccommodation);
                    } else {
                        $messageFormatter->addErrorMessage($additionResult);
                    }
                } else {
                    $messageFormatter->addSuccessRemoveAccommodationMessage($secondAccommodation);
                    $this->dm->flush();
                }
            }
        } else {
            $messageFormatter->addErrorDivideAccommodationMessage();
        }

        return new JsonResponse(json_encode($messageFormatter->getMessages()));
    }

    /**
     * @Method({"GET"})
     * @Route("/packages", name="chessboard_packages", options={"expose"=true})
     * @param Request $request
     * @Security("is_granted('ROLE_PACKAGE_VIEW')")
     * @return JsonResponse
     */
    public function getPackagesData(Request $request)
    {
        $packageData = $this->getChessBoardDataByFilters($request);

        return new JsonResponse($packageData);
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getChessBoardDataByFilters(Request $request)
    {
        $filterData = $this->getFilterData($request);
        $builder = $this->get('mbh.package.report_data_builder')
            ->init($this->hotel,
                $filterData['begin'],
                $filterData['end'],
                $filterData['roomTypeIds'],
                $filterData['housing'],
                $filterData['floor'],
                null,
                $filterData['pageNumber']
            );

        $data['accommodations'] = $builder->getAccommodationIntervals();
        $data['noAccommodationIntervals'] = $builder->getNoAccommodationPackageIntervals();
        $data['leftRoomCounts'] = $builder->getLeftRoomCounts();
        $data['noAccommodationCounts'] = $builder->getDayNoAccommodationPackageCounts();

        return json_encode($data);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getFilterData(Request $request)
    {
        if ($request->isMethod('GET')) {
            $data = $request->query->all();
        } else {
            $data = $request->request->all();
        }

        if (isset($data['filter_begin'])) {
            $beginDate = Helper::getDateFromString($data['filter_begin']);
        } else {
            $beginDate = (new \DateTime('midnight'))->modify('-5 days');
        }
        if (isset($data['filter_end'])) {
            $endDate = Helper::getDateFromString($data['filter_end']);
        } else {
            $endDate = (new \DateTime('midnight'))->modify('+25 days');
        }

        return [
            'begin' => $beginDate,
            'end' => $endDate,
            'roomTypeIds' => $this->helper->getDataFromMultipleSelectField(isset($data['filter_roomType']) ? $data['filter_roomType'] : null),
            'housing' => $this->helper->getDataFromMultipleSelectField(isset($data['housing']) ? $data['housing'] : null),
            'floor' => $this->helper->getDataFromMultipleSelectField(isset($data['floor']) ? $data['floor'] : null),
            'pageNumber' => isset($data['page']) ? $data['page'] : 1
        ];
    }
}