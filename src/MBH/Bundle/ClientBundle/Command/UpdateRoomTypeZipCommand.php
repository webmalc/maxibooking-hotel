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

    const MAX = 60*10;

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

            if (abs($diff) < self::MAX){

                $this->getContainer()->get('mbh_package_zip')->packagesZip();

                $output->writeln(['yes']);

            } else {
                $output->writeln(['no']);
                continue;
            }

        }

    }

}