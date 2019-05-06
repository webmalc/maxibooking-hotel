<?php

namespace MBH\Bundle\BaseBundle\Command;


use MBH\Bundle\BaseBundle\Document\NotificationType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CMErrorNotificationTypeMigrationCommand extends ContainerAwareCommand
{

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('mbh:notification:cm_error_type:migrate')
            ->setDescription('Add channel_manager_error notification type, then run mbh:notification:types:migrate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

        $notificationTypeRepo = $dm->getRepository('MBHBaseBundle:NotificationType');

        $message = '';
        if (!count($notificationTypeRepo->findBy(['type' => NotificationType::CHANNEL_MANAGER_ERROR_TYPE]))) {
            $notificationType = new NotificationType();
            $notificationType
                ->setType(NotificationType::CHANNEL_MANAGER_ERROR_TYPE)
                ->setOwner(NotificationType::OWNER_ERROR)
                ->setIsEnabled(true);
            $dm->persist($notificationType);
            $message .= 'Added notification type channel manager error.';
        } else {
            $message .= 'Notification type already exists.';
        }

        /** @var NotificationType $errorNotificationType */
        $errorNotificationType = $notificationTypeRepo->findOneBy(['type' => NotificationType::ERROR]);

        if ($errorNotificationType) {
            $errorNotificationType->setOwner(NotificationType::OWNER_ERROR);
            $dm->persist($errorNotificationType);
            $message .= ' Error notification type set owner done.';
        }

        $dm->flush();

        $output->writeln($message);

    }
}