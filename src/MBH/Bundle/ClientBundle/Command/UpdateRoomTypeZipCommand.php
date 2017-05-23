<?php

namespace MBH\Bundle\ClientBundle\Command;

use MBH\Bundle\ClientBundle\Document\RoomTypeZip;
use MBH\Bundle\ClientBundle\Document\RoomTypeZipRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRoomTypeZipCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    const MAX = 60 * 40;

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

        /** @var RoomTypeZipRepository $roomTypeZipRepository */
        $roomTypeZipRepository = $this->dm->getRepository('MBHClientBundle:RoomTypeZip');

        /** @var RoomTypeZip $setting */
        $setting = $roomTypeZipRepository->fetchConfig();

        /** @var \DateTime $time */
        foreach ($setting->getTimeDataTimeType() as $time) {
            $diff = $todayTime->getTimestamp() - $time->getTimestamp();
            if (abs($diff) < self::MAX) {
                $this->getContainer()->get('mbh.package_moving')->packagesZip();
                $output->writeln(['completed']);
            }
        }
    }
}