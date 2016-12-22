<?php

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Form\ChessBoardConciseType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
     * @return array
     */
    public function indexAction(Request $request)
    {
        $filterData = $this->getFilterData($request);

        $builder = $this->get('mbh.package.report_data_builder')
            ->init($this->hotel, $filterData['begin'], $filterData['end'],
                $filterData['roomTypeIds'], $filterData['housing']);

        $form = $this->createForm(SearchType::class, null, [
            'security' => $this->container->get('mbh.hotel.selector'),
            'dm' => $this->dm,
            'hotel' => $this->hotel,
            'roomManager' => $this->get('mbh.hotel.room_type_manager')
        ]);

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
            'noAccommodationIntervals' => json_encode($builder->getNoAccommodationIntervals()),
            'leftRoomsJsonData' => json_encode($builder->getLeftRoomCounts()),
            'noAccommodationCounts' => json_encode($builder->getDayNoAccommodationPackageCounts()),
            'roomTypes' => $this->hotel->getRoomTypes(),
            'housings' => $this->hotel->getHousings(),
            'floors' => $this->dm->getRepository('MBHHotelBundle:Room')->fetchFloors(),
        ];
    }

    /**
     * @Method({"GET"})
     * @Route("/packages/{id}", name="chessboard_get_package", options={"expose"=true})
     * @param Package $package
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
     * @return JsonResponse
     */
    public function removePackageAction(Package $package)
    {
        try {
            $this->dm->remove($package);
            $this->dm->flush();
        } catch (\Exception $e) {
            $message = $this->get('translator')->trans($e->getMessage());
            $this->logs($message);
        }

        return new JsonResponse(json_encode(
            [
                'success' => true,
                'messages' => [$this->get('translator')->trans('controller.chessboard.package_remove.success')]
            ]
        ));
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

        $updatedBeginDate = $helper::getDateFromString($request->request->get('begin'));
        $updatedEndDate = $helper::getDateFromString($request->request->get('end'));
        $accommodationId = $request->request->get('accommodationId');
        $updatedRoom = $this->dm->find('MBHHotelBundle:Room', $request->request->get('roomId'));

        $isSuccess = true;
        $messages = [];

        //Если изменяется размещение, а не добавляется новое
        if ($accommodationId != '') {
            $accommodation = $this->dm->find('MBHPackageBundle:PackageAccommodation', $accommodationId);
            //Если удаляется размещение
            if (!$updatedRoom) {
                if ($accommodation->getEnd()->getTimestamp() != $package->getEnd()->getTimestamp()) {
                    $isSuccess = false;
                    $messages[] = $this->get('translator')
                        ->trans('controller.chessboard.accommodation_not_last_remove.error');
                } else {
                    $this->dm->remove($accommodation);
                    $this->dm->flush();
                    $messages[] = [
                        $this->get('translator')->trans('controller.chessboard.accommodation_remove.success', [
                            '%packageId%' => $accommodation->getPackage()->getName(),
                            '%payerInfo%' => $this->getPayerInfo($package),
                            '%begin%' => $accommodation->getBegin()->format('d.m.Y'),
                            '%end%' => $accommodation->getEnd()->format('d.m.Y'),
                        ])
                    ];
                }
            } else {
                $oldPackage = clone $package;
                $accommodation->setAccommodation($updatedRoom);
                $isLastAccommodation = $accommodation == $package->getLastAccommodation();
                $isFirstAccommodation = $accommodation == $package->getFirstAccommodation();
                $isBeginDateChanged = $updatedBeginDate->format('d.m.Y') != $accommodation->getBegin()->format('d.m.Y');
                $isEndDateChanged = $updatedEndDate->format('d.m.Y') != $accommodation->getEnd()->format('d.m.Y');

                //Если изменилась дата или конец размещения, но это не первое и не последнее размещение
                if (($isBeginDateChanged || $isEndDateChanged) && !($isLastAccommodation || $isFirstAccommodation)) {
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
                    $package->setEnd($updatedEndDate);
                    $isPackageChanged = true;
                }

                if ($isPackageChanged) {
                    $this->updatePackageWithAccommodation($oldPackage, $package, $accommodation, $updatedBeginDate,
                        $updatedEndDate, $messages, $isSuccess);
                } else {
                    $accommodation->setBegin($updatedBeginDate);
                    $accommodation->setEnd($updatedEndDate);
                    $this->dm->flush();
                    $messages[] = $this->get('translator')->trans('controller.chessboard.accommodation_update.success');
                }
            }
        } else {
            $accommodation = new PackageAccommodation();
            $accommodation->setBegin($updatedBeginDate);
            $accommodation->setEnd($updatedEndDate);
            $accommodation->setRoom($updatedRoom);
            $package->addAccommodation($accommodation);
            $this->dm->flush();
        }

        $response = [
            'success' => $isSuccess,
            'messages' => $messages
        ];

        return new JsonResponse(json_encode($response));
    }

    private function updatePackageWithAccommodation(
        Package $oldPackage,
        Package $newPackage,
        PackageAccommodation $accommodation,
        \DateTime $updatedBeginDate,
        \DateTime $updatedEndDate,
        &$messages,
        &$isSuccess
    ) {
        $packageValidateErrors = $this->get('validator')->validate($newPackage);
        if (count($packageValidateErrors) === 0) {
            $result = $this->container->get('mbh.order_manager')->updatePackage($newPackage, $oldPackage);
            if ($result instanceof Package) {
                $this->dm->persist($newPackage);
                $this->dm->flush();
                $accommodation->setBegin($updatedBeginDate);
                $accommodation->setEnd($updatedEndDate);
                $this->dm->flush();
                $messages[] = $this->get('translator')->trans('controller.chessboard.package_update.success');
            } else {
                $isSuccess = false;
                $messages[] = [$this->get('translator')->trans($result)];
            }
        } else {
            $isSuccess = false;
            foreach ($packageValidateErrors as $error) {
                $messages[] = $error->getMessage();
            }
        }
    }

    /**
     * @Route("/accommodation_relocate/{id}", name="relocate_accommodation", options={"expose"=true})
     * @param Request $request
     * @param PackageAccommodation $firstAccommodation
     * @return JsonResponse
     * @throws \Exception
     */
    public function relocateAccommodation(Request $request, PackageAccommodation $firstAccommodation)
    {
        $isSuccess = true;
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
            if ($roomId != '') {
                $secondAccommodation = clone $firstAccommodation;
                $secondAccommodation->setBegin($intermediateDate);
                $secondAccommodation->setEnd($accommodationEnd);
                $room = $this->dm->find('MBHHotelBundle:Room', $roomId);
                $secondAccommodation->setRoom($room);
                $package->addAccommodation($secondAccommodation);
                $this->dm->persist($secondAccommodation);
                $messages = [
                    $translator->trans('controller.chessboard.accommodation_divide.success', [
                        '%packageId%' => $package->getName(),
                        '%payerInfo%' => $this->getPayerInfo($package),
                        '%begin%' => $secondAccommodation->getBegin()->format('d.m.Y'),
                        '%end%' => $secondAccommodation->getEnd()->format('d.m.Y'),
                        '%roomName%' => $secondAccommodation->getRoom()->getName()
                    ])
                ];
                $this->dm->flush();
            } else {
                if ($accommodationEnd->getTimestamp() != $package->getEnd()->getTimestamp()) {
                    $messages = [$translator->trans('controller.chessboard.accommodation_not_last_remove.error')];
                    $isSuccess = false;
                } else {
                    $messages = [
                        $translator->trans('controller.chessboard.accommodation_remove.success', [
                            '%packageId%' => $package->getName(),
                            '%payerInfo%' => $this->getPayerInfo($package),
                            '%begin%' => $intermediateDate->format('d.m.Y'),
                            '%end%' => $package->getEnd()->format('d.m.Y'),
                        ])
                    ];
                    $this->dm->flush();
                }
            }
        } else {
            $isSuccess = false;
            $messages = [$translator->trans('controller.chessboard.accommodation_divide.error')];
        }

        return new JsonResponse(json_encode([
            'success' => $isSuccess,
            'messages' => $messages
        ]));
    }

    private function getPayerInfo(Package $package)
    {
        $payerInfo = '';
        if ($package->getPayer()) {
            $payerInfo .= "плательщик \"{$package->getPayer()->getName()}\"";
        }

        return $payerInfo;
    }

    /**
     * @Method({"GET"})
     * @Route("/packages", name="chessboard_packages", options={"expose"=true})
     * @param Request $request
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
                $filterData['roomTypeIds']
            );

        $data['accommodations'] = $builder->getAccommodationData();
        $data['noAccommodationIntervals'] = $builder->getNoAccommodationIntervals();
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
            $endDate = (clone $beginDate)->add(new \DateInterval('P20D'));
        }

        $roomTypeIds = [];
        $roomTypes = $request->get('filter_roomType');
        if (!empty($roomTypes)) {
            if (is_array($roomTypes)) {
                if ($roomTypes[0] != "") {
                    $roomTypeIds = $roomTypes;
                }
            } else {
                $roomTypeIds[] = $roomTypes;
            }
        }
        $housing = $request->get('housing');
        $floor = $request->get('floor');

        return [
            'begin' => $beginDate,
            'end' => $endDate,
            'roomTypeIds' => $roomTypeIds,
            'housing' => $housing,
            'floor' => $floor
        ];
    }

    /**
     * @Route("/test")
     */
    public function testAction()
    {
    }
}