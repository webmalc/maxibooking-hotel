<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;


use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\Translation\TranslatorInterface;

class HomeAway
{
    private $translator;
    private $assignedId;
    //TODO: Мб сменить.
    // Дефолтный id, указанный в параметрах. Используется для проверки того, что для синхронизации был установлен актуальный id
    const DEFAULT_ASSIGNED_ID = 123;

    public function __construct(TranslatorInterface $translator, $assignedId)
    {
        $this->translator = $translator;
        $this->assignedId = $assignedId;
    }

    /**
     * Проверяет тип комнат на заполнение данных и возвращает строку, содержащую информацию о незаполненных данных
     * @param RoomType $roomType
     * @return string
     */
    public function getRoomTypeRequiredDataMessage(RoomType $roomType)
    {
        $requiredRoomTypeData = [];
        strlen($roomType->getDescription() >= 400)
            ?: $requiredRoomTypeData[] = 'home_away.data_to_sync.room_type.description_to_less';
        count($roomType->getImages()) >= 6
            ?: $requiredRoomTypeData[] = 'home_away.data_to_sync.room_type.to_few_images';

        if (count($requiredRoomTypeData) > 0) {
            return $this->translator->trans(
                'home_away.data_to_sinc.room_type_message',
                ['%requested_data%' => join(', ', $requiredRoomTypeData)]
            );
        }

        return '';
    }

    /**
     * @param Hotel $hotel
     * @return string
     */
    public function getHotelRequiredDataMessage(Hotel $hotel)
    {
        $config = $hotel->getHomeAwayConfig();
//        $availableListingCount = 0;
        $requestedData = [];

//        if (!is_null($config)) {
//            foreach ($config->getRooms() as $room) {
//                if ($room->getIsEnabled()) {
//                    $availableListingCount++;
//                }
//            }
//        } else {
//            $requestedData[] = 'home_away.data_to_sync.not_less_than';
//        }

//        if ($availableListingCount < 5) {
//            $requestedData[] = 'home_away.data_to_sync.not_less_than';
//        }
        if (empty($hotel->getLatitude()) || empty($hotel->getLongitude())) {
            $requestedData[] = 'home_away.data_to_sync.fill_longitude_and_latitude';
        }

        if ($this->assignedId == self::DEFAULT_ASSIGNED_ID) {
            $requestedData[] = 'home_away.data_to_sync.assigned_id';
        }

        if (count($requestedData) > 0) {
            return $this->translator->trans(
                'home_away.data_to_sinc.hotel_message',
                ['%requested_data%' => join(', ', $requestedData)]
            );
        }

        return '';
    }
}