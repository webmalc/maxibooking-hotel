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

        if (!count($notificationTypeRepo->findBy(['type' => 'channel_manager_error']))) {
            $notificationType = new NotificationType();
            $notificationType
                ->setType('channel_manager_error')
                ->setOwner('stuff')
                ->setIsEnabled(true);
            $dm->persist($notificationType);
            $dm->flush();

            $notificationTypesMigrationCommand = $this->getApplication()->find('mbh:notification:types:migrate');
            $notificationTypesMigrationCommand->run(new ArrayInput([]), $output);
        }
    }
}