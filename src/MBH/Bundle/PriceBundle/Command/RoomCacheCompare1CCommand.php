<?php

namespace MBH\Bundle\PriceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class RoomCacheCompare1CCommand extends ContainerAwareCommand
{
    const FILE_PATH = '/../protectedUpload/1C/';

    const HOTELS = [
        1 => 'Пансионат АзовЛенд',
        2 => 'Пансионат Азовский',
        3 => 'Парк - отель "РИО"'
    ];

    protected function configure()
    {
        $this
            ->setName('azovsky:cache:compare')
            ->setDescription('Compare 1C room cache')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();

        $result = $this->compare();

        $time = $start->diff(new \DateTime());
        $output->writeln(
            sprintf('Compare %s. Elapsed time: %s', $result ? 'complete' : 'aborted', $time->format('%H:%I:%S'))
        );
    }

    private function compare(): bool
    {
        $path = $uploadedPath = $this->getContainer()->get('kernel')->getRootDir() . '/../protectedUpload/1C/1c_compare.xml';
        $helper = $this->getContainer()->get('mbh.helper');
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();


        if (!file_exists($path) || !is_readable($path)) {
            $this->sendMessage([
                'error' => 'Файл отчета не загружен.'
            ]);

            return false;
        }

        $xml = simplexml_load_file($path);
        if (!$xml) {
            $this->sendMessage([
                'error' => 'Неверный формат файла XML.'
            ]);

            return false;
        }

        $begin = $helper->getDateFromString((string)$xml->BEGIN);
        $end = $helper->getDateFromString((string)$xml->END);

        foreach ($xml->HOTEL as $hotelXml) {
            if (empty(self::HOTELS[(int)$hotelXml->ID])) {
                continue;
            }

            $hotel = $dm->getRepository('MBHHotelBundle:Hotel')->findOneBy([
                '$or' => [['title' => self::HOTELS[(int)$hotelXml->ID]], ['fullTitle' => self::HOTELS[(int)$hotelXml->ID]]]
            ]);

            if (!$hotel) {
                $this->sendMessage([
                    'error' => 'Не найден отель <' + $hotel + '>.'
                ]);

                return false;
            }
            
            $caches = $dm->getRepository('MBHPriceBundle:RoomCache')->fetch($begin, $end, $hotel, [], false, true);

            foreach ($hotelXml->ROOM as $roomXml) {

                $roomType = $dm->getRepository('MBHHotelBundle:RoomType')->findOneBy([
                    '$or' => [['title' => (string)$roomXml->TITLE], ['fullTitle' => (string)$roomXml->TITLE]]
                ]);
                dump((string)$roomXml->TITLE);
            }
        }

        return true;
    }

    private function sendMessage(array $data)
    {
        $notifier = $this->getContainer()->get('mbh.notifier.mailer');
        $message = $notifier::createMessage();
        $message
            ->setText('hide')
            ->setFrom('report')
            ->setSubject('mailer.compare.1c.subject')
            ->setType('info')
            ->setCategory('report')
            ->setAdditionalData($data)
            ->setTemplate('MBHBaseBundle:Mailer:compare1C.html.twig')
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'))
        ;
        $notifier
            ->setMessage($message)
            ->notify()
        ;
    }

}