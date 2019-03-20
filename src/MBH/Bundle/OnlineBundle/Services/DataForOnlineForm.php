<?php
/**
 * Date: 20.03.19
 */

namespace MBH\Bundle\OnlineBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use Symfony\Component\HttpFoundation\Request;

class DataForOnlineForm
{
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var FormConfig
     */
    private $formConfig;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array|Hotel[]
     */
    private $hotels;

    public function __construct(DocumentManager $dm, Helper $helper)
    {
        $this->dm = $dm;
        $this->helper = $helper;
    }

    public function setFormConfig(FormConfig $formConfig): self
    {
        $this->formConfig = $formConfig;

        return $this;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param FormConfig $formConfig
     * @param Request|null $request
     * @return array|Hotel[]
     */
    public function getHotels(): array
    {
        if ($this->hotels === null) {
            $hotelsQb = $this->dm->getRepository('MBHHotelBundle:Hotel')
                ->createQueryBuilder()
                ->sort('fullTitle', 'asc');

            $configHotelsIds = $this->helper->toIds($this->formConfig->getHotels());

            $hotels = [];
            /** @var Hotel $hotel */
            foreach ($hotelsQb->getQuery()->execute() as $hotel) {
                if ($configHotelsIds && !in_array($hotel->getId(), $configHotelsIds)) {
                    continue;
                }

                foreach ($hotel->getTariffs() as $tariff) {
                    if ($tariff->getIsOnline()) {
                        $hotels[] = $hotel;
                        break;
                    }
                }
            }

            $this->hotels = $hotels;
        }

        return $this->hotels;
    }

    public function getRoomTypes(): array
    {
        $choices = $this->formConfig->getRoomTypeChoices()->toArray();

        if (count($choices) === 0) {
            /** @var Hotel $hotel */
            $roomTypes = [];
            foreach ($this->getHotels() as $hotel) {
                $roomTypes[] = $hotel->getRoomTypes()->toArray();
            }

            $choices = array_merge(...$roomTypes);
        }

        $this->filterRoomTypeIfIssetHotelInRequest($choices);

        return $choices;
    }

    private function filterRoomTypeIfIssetHotelInRequest(array &$chooseRoomType): void
    {
        if ($this->request === null) {
            return;
        }

        $hotelId = $this->request->get('hotel');

        if (empty($hotelId)) {
            return;
        }

        /** @var RoomType $roomType */
        foreach ($chooseRoomType as $index => $roomType) {
            if ($roomType->getHotel()->getId() !== $hotelId) {
                unset($chooseRoomType[$index]);
            }
        }

    }
}