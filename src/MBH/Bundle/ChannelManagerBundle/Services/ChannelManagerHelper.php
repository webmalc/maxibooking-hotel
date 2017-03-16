<?php

namespace MBH\Bundle\ChannelManagerBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Translation\TranslatorInterface;

class ChannelManagerHelper
{
    private $isRoomTypesInit = false;
    private $roomTypes;
    private $isTariffsSyncDataInit = false;
    private $tariffsSyncData;
    private $notifier;
    private $translator;
    private $dm;

    public function __construct(Notifier $notifier, TranslatorInterface $translator, DocumentManager $dm)
    {
        $this->notifier = $notifier;
        $this->translator = $translator;
        $this->dm = $dm;
    }

    /**
     * Ленивая загрузка массива, содержащего данные о синхронизации типов комнат сервиса и отеля
     *
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    public function getRoomTypesSyncData(ChannelManagerConfigInterface $config, $byService = false)
    {

        if (!$this->isRoomTypesInit) {

            foreach ($config->getRooms() as $room) {
                /** @var Room $room */
                $roomType = $room->getRoomType();
                if (empty($room->getRoomId()) || !$roomType->getIsEnabled() || !empty($roomType->getDeletedAt())) {
                    continue;
                }

                if ($byService) {
                    $this->roomTypes[$room->getRoomId()] = [
                        'syncId' => $room->getRoomId(),
                        'doc' => $roomType
                    ];
                } else {
                    $this->roomTypes[$roomType->getId()] = [
                        'syncId' => $room->getRoomId(),
                        'doc' => $roomType
                    ];
                }
            }

            $this->isRoomTypesInit = true;
        }

        return $this->roomTypes;
    }

    /**
     * Ленивая загрузка массива, содержащего данные о синхронизации тарифов сервиса и отеля
     *
     * @param ChannelManagerConfigInterface $config
     * @param bool $byService
     * @return array
     */
    public function getTariffsSyncData(ChannelManagerConfigInterface $config, $byService = false)
    {
        if (!$this->isTariffsSyncDataInit) {

            foreach ($config->getTariffs() as $configTariff) {
                /** @var \MBH\Bundle\ChannelManagerBundle\Document\Tariff $configTariff */
                $tariff = $configTariff->getTariff();

                if ($configTariff->getTariffId() === null || !$tariff->getIsEnabled() || !empty($tariff->getDeletedAt())) {
                    continue;
                }

                if ($byService) {
                    $this->tariffsSyncData[$configTariff->getTariffId()] = [
                        'syncId' => $configTariff->getTariffId(),
                        'doc' => $tariff
                    ];
                } else {
                    $this->tariffsSyncData[$tariff->getId()] = [
                        'syncId' => $configTariff->getTariffId(),
                        'doc' => $tariff
                    ];
                }
            }

            $this->isTariffsSyncDataInit = true;
        }

        return $this->tariffsSyncData;
    }

    public function notify(Order $order, $service, $type = 'new')
    {
        try {
            $message = $this->notifier->createMessage();

            $text = 'channelManager.' . $service . '.notification.' . $type;
            $subject = 'channelManager.' . $service . '.notification.subject.' . $type;

            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->enable('softdeleteable');
            }

            $packages = [];
            $hotel = null;

            foreach ($order->getPackages() as $package) {
                /** @var Package $package */
                if ($package->getDeletedAt()) {
                    continue;
                }
                $packages[] = $package->getNumberWithPrefix();
                if (!$hotel) {
                    $hotel = $package->getRoomType()->getHotel();
                }
            }
            if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
                $this->dm->getFilterCollection()->disable('softdeleteable');
            }

            $message
                ->setText($this->translator->trans($text, ['%order%' => $order->getId(), '%packages%' => implode(', ', $packages)],
                    'MBHChannelManagerBundle'))
                ->setFrom('channelmanager')
                ->setSubject($this->translator->trans($subject, [], 'MBHChannelManagerBundle'))
                ->setType($type == 'delete' ? 'danger' : 'info')
                ->setCategory('notification')
                ->setAutohide(false)
                ->setHotel($hotel)
                ->setOrder($order)
                ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
                ->setEnd(new \DateTime('+10 minute'));

            $this->notifier->setMessage($message)->notify();

        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}