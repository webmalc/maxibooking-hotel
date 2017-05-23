<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 22.05.17
 * Time: 15:41
 */

namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\PackageBundle\Document\PackageMovingInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/report/package_moving")
 * Class PackageMovingReportController
 * @package MBH\Bundle\PackageBundle\Controller
 */
class PackageMovingController extends BaseController
{
    /**
     * @Route("/", name="package_moving")
     * @Template()
     * @Security("is_granted('ROLE_PACKAGE_MOVING')")
     * @param Request $request
     * @return array
     */
    public function packageMovingAction(Request $request)
    {
        $packageMovingInfo = $this->dm
            ->getRepository('MBHPackageBundle:PackageMovingInfo')
            ->createQueryBuilder()
            ->limit(1)
            ->field('status')->notEqual(PackageMovingInfo::OLD_REPORT_STATUS)
            ->getQuery()
            ->getSingleResult();

        if ($request->isMethod('POST') && is_null($packageMovingInfo)) {
            $helper = $this->get('mbh.helper');
            $user = $this->getUser();
            $begin = $helper->getDateFromString($request->request->get('begin'));
            $end = $helper->getDateFromString($request->request->get('end'));
            $roomTypeIds = $request->request->get('roomType') ?? [];

            $packageMovingInfo = (new PackageMovingInfo())
                ->setRunningBy($user)
                ->setStartAt(new \DateTime())
                ->setBegin($begin)
                ->setEnd($end);

            foreach ($roomTypeIds as $roomTypeId) {
                if ($roomTypeId != '') {
                    $roomType = $this->dm->find('MBHHotelBundle:RoomType', $roomTypeId);
                    $packageMovingInfo->addRoomType($roomType);
                }
            }

            $this->dm->persist($packageMovingInfo);
            $this->dm->flush();

            $this->container->get('old_sound_rabbit_mq.task_prepare_package_moving_report_producer')
                ->publish(
                    serialize(
                        [
                            'packageMovingInfoId' => $packageMovingInfo->getId()
                        ]
                    )
                );
        }

        return [
            'roomTypes' => $this->hotel->getRoomTypes(),
            'movingInfo' => $packageMovingInfo,
        ];
    }

    /**
     * @Security("is_granted('ROLE_PACKAGE_MOVING')")
     * @ParamConverter("packageMovingInfo", class="MBHPackageBundle:PackageMovingInfo", options={"id" = "movingInfoId"})
     * @Route("/package_move/{movingInfoId}/{movingPackageId}", name="package_move", options={"expose" = true})
     * @param PackageMovingInfo $packageMovingInfo
     * @param $movingPackageId
     * @return JsonResponse
     * @throws Exception
     */
    public function movePackageAction(PackageMovingInfo $packageMovingInfo, $movingPackageId)
    {
        $isSuccess = true;
        $movingPackageData = $packageMovingInfo->getMovingPackageDataById($movingPackageId);
        if (is_null($movingPackageData)) {
            throw new Exception('Не найден объект, хранящий данные о перемещении брони в меньший тип номера');
        } elseif (!$movingPackageData->getIsMoved()) {
            $roomTypeChangingResult = $this->get('mbh.order_manager')
                ->changeRoomType($movingPackageData->getPackage(), $movingPackageData->getNewRoomType());
            if ($roomTypeChangingResult) {
                $movingPackageData->setMovingData($this->getUser());
                $this->dm->flush();
            } else {
                $isSuccess = false;
                $errorMessage = 'Невозможно изменить тип комнат брони "'
                    . $movingPackageData->getPackage()->getNumberWithPrefix()
                    . '" по причине отсутствия свободных мест.';
            }
        }

        $result = ['success' => $isSuccess];
        if (isset($errorMessage)) {
            $result['error'] = $errorMessage;
        }

        return new JsonResponse($result);
    }

    /**
     * @Security("is_granted('ROLE_PACKAGE_MOVING')")
     * @ParamConverter("packageMovingInfo", class="MBHPackageBundle:PackageMovingInfo", options={"id" = "movingInfoId"})
     * @Route("/close_moving_report/{movingInfoId}", name="close_moving_report", options={"expose" = true})
     * @param PackageMovingInfo $packageMovingInfo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function closePackageMovingReport(PackageMovingInfo $packageMovingInfo)
    {
        $packageMovingInfo->setStatus(PackageMovingInfo::OLD_REPORT_STATUS, true);
        $this->dm->flush();
        $this->get('mbh.package_moving')->sendPackageMovingMail($packageMovingInfo,
            'mailer.close_package_moving_report.text', 'mailer.close_package_moving_report.subject',
            'MBHBaseBundle:Mailer:close_package_moving_report.html.twig');

        return $this->redirectToRoute('package_moving');
    }

    /**
     * @Security("is_granted('ROLE_PACKAGE_MOVING')")
     * @Route("/is_report_ready/{movingInfoId}", name="is_report_ready", options={"expose" = true})
     * @ParamConverter("packageMovingInfo", class="MBHPackageBundle:PackageMovingInfo", options={"id" = "movingInfoId"})
     * @param PackageMovingInfo $packageMovingInfo
     * @return JsonResponse
     */
    public function isReportReady(PackageMovingInfo $packageMovingInfo)
    {
        return new JsonResponse(['ready' => $packageMovingInfo->getStatus() === PackageMovingInfo::READY_STATUS]);
    }

    /**
     * @Template()
     * @Route("/report", name="package_moving_report")
     * @param Request $request
     * @return array
     */
    public function packageMovingReportAction(Request $request)
    {
        $requestBegin = $request->get('begin');
        if (!empty($request->get('begin'))) {
            $begin = $this->helper->getDateFromString($requestBegin);
        } else {
            $begin = new \DateTime('midnight');
        }

        $requestEnd = $request->get('end');
        if (!empty($requestEnd)) {
            $end = $this->helper->getDateFromString($requestEnd);
        } else {
            $end = new \DateTime('midnight +1 month');
        }

        $userIds = $request->get('user');
        if (!empty($userIds) && is_array($userIds) && $userIds[0] != '') {
            $users = $this->dm
                ->getRepository('MBHUserBundle:User')
                ->getByIds($userIds)
                ->toArray();
        } else {
            $users = null;
        }

        $requestHotelIds = $request->get('hotel');
        if (!empty($requestHotelIds) && is_array($requestHotelIds) && $requestHotelIds[0] != '') {
            $hotels = $this->dm
                ->getRepository('MBHHotelBundle:Hotel')
                ->getHotelsByIds($requestHotelIds)
                ->toArray();
        } else {
            $hotels = null;
        }

        $movedPackagesData = $this->get('mbh.package_moving')->getMovedPackagesData($begin, $end, $users, $hotels);

        return [
            'chosenHotels' => $hotels,
            'chosenUsers' => $users,
            'begin' => $begin,
            'end' => $end,
            'movingPackagesData' => $movedPackagesData,
            'hotels' => $this->dm->getRepository('MBHHotelBundle:Hotel')->findAll(),
            'users' => $this->dm->getRepository('MBHUserBundle:User')->findAll()
        ];
    }
}