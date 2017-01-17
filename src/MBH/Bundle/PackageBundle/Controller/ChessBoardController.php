<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Form\ChessBoardConciseType;
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
    /**
     * @Route("/", name="chess_board_home")
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
                $filterData['roomTypeIds'], $filterData['housing'], $filterData['floor']);

        $form = $this->createForm(SearchType::class, null, [
            'security' => $this->container->get('mbh.hotel.selector'),
            'dm' => $this->dm,
            'hotel' => $this->hotel,
            'roomManager' => $this->get('mbh.hotel.room_type_manager')
        ]);

        $rightsChecker = $this->get('security.authorization_checker');
        $canCreatePackage = $rightsChecker->isGranted('ROLE_PACKAGE_NEW')
            && $rightsChecker->isGranted('ROLE_SEARCH') ? 'true' : 'false';

        return [
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
            'roomTypes' => $this->hotel->getRoomTypes(),
            'housings' => $this->hotel->getHousings(),
            'floors' => $this->dm->getRepository('MBHHotelBundle:Room')->fetchFloors(),
            'canCreatePackage' => $canCreatePackage
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
            //TODO: Добавить проверку прав для изменения только размещения
            $accommodation->setBegin($updatedBeginDate);
            $accommodation->setEnd($updatedEndDate);
            $this->dm->flush();
            $messageFormatter->addSuccessUpdateAccommodationMessage();
        }
    }

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
        )) {
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
        if (!($rightsChecker->isGranted('ROLE_PACKAGE_EDIT')  && ($rightsChecker->isGranted('ROLE_PACKAGE_EDIT_ALL')
            || $rightsChecker->isGranted('EDIT', $oldPackage))
        )) {
            throw $this->createAccessDeniedException();
        }

        $packageValidateErrors = $this->get('validator')->validate($newPackage);
        if (count($packageValidateErrors) === 0) {
            $result = $this->container->get('mbh.order_manager')->updatePackage($oldPackage, $newPackage);
            if ($result instanceof Package) {
                $this->dm->persist($newPackage);
                $this->dm->flush();
                $accommodation->setBegin($updatedBeginDate);
                $accommodation->setEnd($updatedEndDate);
                $this->dm->flush();
                $messageFormatter->addSuccessPackageUpdateMessage();
            } else {
                $this->dm->persist($oldPackage);
                $this->dm->flush();
                $messageFormatter->addErrorMessage($result);
            }
        } else {
            foreach ($packageValidateErrors as $error) {
                $messageFormatter->addErrorMessage($error);
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
            $firstAccommodation->setEnd($intermediateDate);
            $secondAccommodation = clone $firstAccommodation;
            $secondAccommodation->setBegin($intermediateDate);
            $secondAccommodation->setEnd($accommodationEnd);

            if ($roomId != '') {
                $room = $this->dm->find('MBHHotelBundle:Room', $roomId);
                $secondAccommodation->setRoom($room);
                $package->addAccommodation($secondAccommodation);
                $this->dm->persist($secondAccommodation);
                $messageFormatter->addSuccessAddAccommodationMessage($secondAccommodation);
                $this->dm->flush();
            } else {
                $messageFormatter->addSuccessRemoveAccommodationMessage($secondAccommodation);
                $this->dm->flush();
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
                $filterData['floor']
            );

        $data['accommodations'] = $builder->getAccommodationIntervals();
        $data['noAccommodationIntervals'] = $builder->getNoAccommodationPackageIntervals();
        $data['leftRoomCounts'] = $builder->getLeftRoomCounts();
        $data['noAccommodationCounts'] = $builder->getDayNoAccommodationPackageCounts();

        return json_encode($data);
    }

    private function getFilterData(Request $request)
    {
        $helper = $this->container->get('mbh.helper');
        $beginDate = $helper->getDateFromString($request->get('filter_begin'));
        if (!$beginDate) {
            $beginDate = new \DateTime('00:00');
            $beginDate->modify('-5 days');
        }
        $endDate = $helper->getDateFromString($request->get('filter_end'));
        if (!$endDate || $endDate->diff($beginDate)->format("%a") > 160 || $endDate <= $beginDate) {
            $endDate = (clone $beginDate)->add(new \DateInterval('P25D'));
        }

        return [
            'begin' => $beginDate,
            'end' => $endDate,
            'roomTypeIds' => $this->getDataFromMultipleSelectField($request->get('filter_roomType')),
            'housing' => $this->getDataFromMultipleSelectField($request->get('housing')),
            'floor' => $this->getDataFromMultipleSelectField($request->get('floor'))
        ];
    }

    private function getDataFromMultipleSelectField($fieldData)
    {
        if (!empty($fieldData) && is_array($fieldData) && $fieldData[0] != '') {
            return $fieldData;
        }

        return [];
    }
}