<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MBH\Bundle\BaseBundle\Controller\BaseController;

/**
 * @Route("/cm_external_api")
 * Class ChannelManagerApiController
 * @package MBH\Bundle\ChannelManagerBundle\Controller
 */
class ChannelManagerApiController extends BaseController
{
    /**
     * @Route("/ical_room_calendar/{service}/{id}", name="ical_room_calendar")
     * @param string $channelManager
     * @param RoomType $roomType
     * @return Response
     */
    public function roomCalendarAction(string $channelManager, RoomType $roomType)
    {
        $calendar = $this
            ->get('mbh.channelmanager')
            ->getServiceIdByName($channelManager)
            ->generateRoomCalendar($roomType);

        return new Response($calendar, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="cal.ics"'
        ]);
    }
}