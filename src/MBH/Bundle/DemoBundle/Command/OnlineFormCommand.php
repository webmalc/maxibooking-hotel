<?php

namespace MBH\Bundle\DemoBundle\Command;

use MBH\Bundle\OnlineBundle\Document\FormConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OnlineFormCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:demo:online_form')
            ->setDescription('Create demo online form')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $path = $this->getContainer()->get('kernel')->getRootDir() . '/../web/demo/';
        $indexPath = $path . 'index.html';
        $resultsPath = $path . 'results.html';

        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $hotel = $dm->getRepository('MBHHotelBundle:Hotel')->findOneBy([]);

        if (!$hotel) {
            $output->writeln('Error. New online form not created. Hotel not found!!!');
            return false;
        }

        if (!file_exists($indexPath) || !is_readable($indexPath)) {
            $output->writeln('Error. New online form not created. index.html file not exist!!!');
            return false;
        }

        if (!file_exists($resultsPath) || !is_readable($resultsPath)) {
            $output->writeln('Error. New online form not created. results.html file not exist!!!');
            return false;
        }

        //create FormConfig
        $formConfig = $dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy([]);

        if(!$formConfig) {
            $formConfig = new FormConfig();
            $formConfig->setEnabled(true)
                ->setRoomTypes(true)
                ->setTourists(true)
                ->setPaymentTypes(['in_hotel'])
                ;
            $dm->persist($formConfig);
            $dm->flush();
        }

        // replace code
        foreach ([$indexPath, $resultsPath] as $changePath) {

            $changeString = file_get_contents($changePath);

            $search = '{[{project_name}]}';
            $replace = $hotel->getFullTitle();
            $changeString = str_replace($search, $replace, $changeString);
            file_put_contents($changePath, $changeString);
        }

        $output->writeln('Complete. New online form created.');
    }
}