<?php

namespace MBH\Bundle\BaseBundle\Command;

use MBH\Bundle\BaseBundle\Document\Currency;
use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CurrencyLoadCommand extends ContainerAwareCommand
{
    const SOURCE_LINK = "http://www.cbr.ru/scripts/XML_daily.asp?date_req=";

    protected function configure()
    {
        $this
            ->setName('mbh:currency:load')
            ->setDescription('Load currencies from http://www.cbr.ru/');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $validator = $this->getContainer()->get('validator');
        $date = new \DateTime('midnight');
        $xml = simplexml_load_file(self::SOURCE_LINK.$date->format('d/m/Y'));

        if (!$xml) {
            throw new Exception('Invalid xml');
        }

        foreach ($xml->Valute as $info) {

            $currency = new Currency();
            $currency
                ->setCode((string)$info->CharCode)
                ->setDate($date)
                ->setRatio((float)str_replace(',', '.', $info->Value) / (int)$info->Nominal)
                ->setTitle((string)$info->Name)
            ;

            if (count($validator->validate($currency)) <= 0) {
                $dm->persist($currency);
                $dm->flush();
            }
        }

        $time = $start->diff(new \DateTime());
        $output->writeln('Loading complete. Elapsed time: '.$time->format('%H:%I:%S'));
    }
}