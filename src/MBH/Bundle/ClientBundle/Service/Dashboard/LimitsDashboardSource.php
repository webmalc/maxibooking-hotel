<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Service\ClientLimitsManager;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LimitsDashboardSource extends AbstractDashboardSource
{
    /** @var  ClientLimitsManager */
    private $limitsManager;

    public function __construct(
        ManagerRegistry $documentManager,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        Helper $helper,
        ClientLimitsManager $limitsManager
    ) {
        parent::__construct($documentManager, $validator, $translator, $helper);
        $this->limitsManager = $limitsManager;
    }

    protected function generateMessages(): array
    {
        $begin = new \DateTime('midnight');
        $end = new \DateTime('midnight + 1 year');
        $messages = [];
        if ($this->limitsManager->isLimitOfRoomsExceeded()) {
            $messages[] = $this->translator->trans('room_controller.limit_of_rooms_exceeded');
        }

        $outOfLimitRoomsDays = $this->limitsManager->getDaysWithExceededLimitNumberOfRoomsInSell($begin, $end);
        if (count($outOfLimitRoomsDays) > 0) {
            $messages[] = $this->translator->trans('room_cache_controller.limit_of_rooms_exceeded', [
                '%busyDays%' => join(', ', $outOfLimitRoomsDays)
            ]);
        }

        return $messages;
    }
}