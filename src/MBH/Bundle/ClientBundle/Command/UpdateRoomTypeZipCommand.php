<?php

namespace MBH\Bundle\ClientBundle\Command;

use MBH\Bundle\ClientBundle\Document\RoomTypeZip;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UpdateRoomTypeZipCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    const MAX = 60 * 10;

    protected function configure()
    {
        $this
            ->setName('mbh:client:roomTypeZip:update')
            ->setDescription('Update RoomTypeZip');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $todayTime = new \DateTime();
        $todayTime->format('H:i');

        /** @var RoomTypeZip $setting */
        $setting = $this->dm->getRepository('MBHClientBundle:RoomTypeZip')->fetchConfig();

        foreach ($setting->getTimeDataTimeType() as $time) {

            $diff = $todayTime->getTimestamp() - $time->getTimestamp();

            if (abs($diff) < self::MAX) {

                $result = $this->getContainer()->get('mbh_package_zip')->packagesZip();

                if ($result === true) {
                    $this->sendMessage();
                    $output->writeln('message sent');
                }

                $output->writeln(['completed']);
            }
        }
    }

    private function sendMessage()
    {
        $container = $this->getContainer();
        $notifier = $container->get('mbh.notifier.mailer');
        $message = $notifier::createMessage();

        $info['link'] = $container->get('router')->generate('package_moving', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $info['linkText'] = 'mailer.package_zip.button_lin_text';

        $message
            ->setText('mailer.packageZip.text')
            ->setFrom('system')
            ->setSubject('mailer.packageZip.subject')
            ->setType('info')
            ->setCategory('notification')
            ->setTemplate('MBHBaseBundle:Mailer:packageZip.html.twig')
            ->setAdditionalData($info)
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'));

        $notifier
            ->setMessage($message)
            ->notify();
    }

}