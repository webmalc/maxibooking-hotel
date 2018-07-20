<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class ProgramNotPaidSource extends AbstractDashboardSource
{
    const WARNING_DISPLAYS_IN_DAYS = 3;
    const TYPE = 'danger';

    private $clientManager;
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

    protected function generateMessages(): array
    {
        $messages = [];
        $numberOfDaysBeforeDisable = $this->clientManager->getNumberOfDaysBeforeDisable();
        if (!is_null($numberOfDaysBeforeDisable) && $numberOfDaysBeforeDisable <= self::WARNING_DISPLAYS_IN_DAYS) {
            $messages[] = $this->translator->trans('program_not_paid.error_messages', [
                '%days%' => $numberOfDaysBeforeDisable,
                '%payment_url%' => $this->router->generate('user_payment')
            ]);
        }

        return $messages;
    }
}