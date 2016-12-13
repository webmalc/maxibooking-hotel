<?php

namespace MBH\Bundle\ClientBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRoomTypeZipCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    const MAX = 60 * 1000;

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

        //roomTypeZip
        $setting = $this->dm->getRepository('MBHClientBundle:RoomTypeZip')->fetchConfig();

            foreach ($setting->getTimeDataTimeType() as $time) {

                $diff = $todayTime->getTimestamp() - $time->getTimestamp();

                if (abs($diff) < self::MAX) {

                    $info = $this->getContainer()->get('mbh_package_zip')->packagesZip();

                    $this->sendMessage($info);

                    $output->writeln(['completed']);

                }

            }

    }

    /**
     * @param $info
     */
    private function sendMessage($info)
    {
        if (!$info['error'] == 0) {

            $container = $this->getContainer();

            $notifier = $container->get('mbh.notifier.mailer');

            $message = $notifier::createMessage();
            $message
                ->setText('mailer.packageZip.text')
                ->setFrom('system')
                ->setSubject('mailer.packageZip.subject')
                ->setType('info')
                ->setCategory('notification')
                ->setTemplate('MBHBaseBundle:Mailer:packageZip.html.twig')
                ->setAdditionalData([
                    'error' => $info['error'],
                    'amount' => $info['amount'],
                ])
                ->setAutohide(false)
                ->setEnd(new \DateTime('+1 minute'));

            $notifier
                ->setMessage($message)
                ->notify();

        }

    }

}