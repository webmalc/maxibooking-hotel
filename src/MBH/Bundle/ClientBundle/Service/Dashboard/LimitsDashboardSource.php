<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LimitsDashboardSource extends AbstractDashboardSource
{
    /**
     * message default type
     */
    const TYPE = 'danger';
    /** @var  ClientManager */
    private $clientManager;
    /** @var  Router */
    private $router;

    public function __construct(
        ManagerRegistry $documentManager,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        Helper $helper,
        ClientManager $clientManager,
        Router $router
    ) {
        parent::__construct($documentManager, $validator, $translator, $helper);
        $this->clientManager = $clientManager;
        $this->router = $router;
    }

    /**
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    protected function generateMessages(): array
    {
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight + 1 year');
        $messages = [];
        if (!$this->clientManager->isDefaultClient()) {
            if ($this->clientManager->isLimitOfRoomsExceeded()) {
                $messages[] = $this->translator->trans('room_controller.limit_of_room_fund_exceeded', [
                    '%availableNumberOfRooms%' => $this->clientManager->getAvailableNumberOfRooms(),
                    '%overviewUrl%' => $this->router->generate('total_rooms_overview')
                ]);
            }

            $outOfLimitRoomsDays = $this->clientManager->getDaysWithExceededLimitNumberOfRoomsInSell($begin, $end);
            if (count($outOfLimitRoomsDays) > 0) {
                $messages[] = $this->translator
                    ->trans('room_cache_controller.limit_of_rooms_exceeded', [
                        '%busyDays%' => join(', ', $outOfLimitRoomsDays),
                        '%availableNumberOfRooms%' => $this->clientManager->getAvailableNumberOfRooms(),
                        '%overviewUrl%' => $this->router->generate('total_rooms_overview')
                    ]);
            }
        }

        return $messages;
    }
}