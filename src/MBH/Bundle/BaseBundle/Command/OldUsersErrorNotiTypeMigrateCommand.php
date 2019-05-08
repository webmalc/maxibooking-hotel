<?php

namespace MBH\Bundle\BaseBundle\Command;


use MBH\Bundle\BaseBundle\Document\NotificationType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OldUsersErrorNotiTypeMigrateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:error_notification:migration')
            ->setDescription('Add error notification type for old clients <= 22.05.18 ');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
        $notificationTypeRepo = $dm->getRepository(NotificationType::class);

        $errorNotificationType = $notificationTypeRepo->findOneBy(['type' => NotificationType::ERROR]);

        if (!$errorNotificationType) {
            $errorType = new NotificationType();
            $errorType
                ->setType(NotificationType::ERROR)
                ->setOwner(NotificationType::OWNER_ERROR)
                ->setIsEnabled(true);
            $dm->persist($errorType);
            $dm->flush();
            $output->writeln('added new error notification type with "error" owner');
        } else {
            $output->writeln('everything is up to date');
        }
    }
}
