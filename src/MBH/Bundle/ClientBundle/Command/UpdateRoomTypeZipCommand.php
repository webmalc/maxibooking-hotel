<?php

namespace MBH\Bundle\ClientBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\DateTime;


class UpdateRoomTypeZipCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    const maxMin = 10;

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
        $times = $setting->getTimes();

        foreach ($setting->getTime() as $time) {

            $time = $times[$time];
            $dated = \DateTime::createFromFormat('H:i', $time);
            $lastedTime= $dated->diff($todayTime);

            if ((int)$lastedTime->format('%H') == 0 && (int)$lastedTime->format('%i') < self::maxMin){

                $roomResponse = $this->getContainer()->get('mbh_package_zip')->packagesZip();

                $output->writeln(['yes']);

            } else {
                continue;
            }

            // outputs multiple lines to the console (adding "\n" at the end of each line)
            $output->writeln([
                (string)$todayTime->format('H:i'),
                (string)$dated->format('H:i'),
                (int)$lastedTime->format('%H'),
                (int)$lastedTime->format('%i'),

            ]);

        }

    }

}