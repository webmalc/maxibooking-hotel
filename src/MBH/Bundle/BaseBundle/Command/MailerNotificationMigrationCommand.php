<?php


namespace MBH\Bundle\BaseBundle\Command;


use Doctrine\MongoDB\Cursor;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailerNotificationMigrationCommand extends ContainerAwareCommand
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('mbh:notification:types:migrate')
            ->setDescription('Add all notification types to according entities')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $notificationRepository = $dm->getRepository('MBHBaseBundle:NotificationType');
        /** @var ClientConfig $clientConfig */
        $clientConfig = $dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
        /** @var Cursor $clientTypes */
        $clientTypes = $notificationRepository->getClientType();
        if (count($clientTypes)) {
            $clientConfig->setAllowNotificationTypes($clientTypes->toArray());
        }
        $users = $dm->getRepository('MBHUserBundle:User')->findAll();
        /** @var Cursor $userTypes */
        $userTypes = $notificationRepository->getStuffType();
        foreach ($users as $user) {
            /** @var User $user */
            $user->setAllowNotificationTypes($userTypes->toArray());
        }

        $dm->flush();
    }


}