<?php

namespace MBH\Bundle\FMSBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\FMSBundle\Document\FMSLog;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FMSImportDataCommand extends ContainerAwareCommand
{
    const DAY_DELAY = 7;

    /**
     * @var DocumentManager
     */
    protected $dm;

    protected function configure()
    {
        $this
            ->setName('mbh:fms:import')
            ->setDescription('Send data to fms');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkDaysAmount();
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::__construct();
        $this->dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager')->getManager();
    }

    private function checkDaysAmount()
    {
        $currentDate = new \DateTime();

        //get last entry
        $lastLogEntryArray = $this->dm->getRepository('MBHFMSBundle:FMSLog')->findBy([], ['sendAt' => 'desc'], 1);
        /** @var  FMSLog */
        $lastLogEntry = $lastLogEntryArray[0];
        // получение данных о последней записи
        $dateDifference = $currentDate->diff($lastLogEntry->getSendAt())->days;

        if($dateDifference > self::DAY_DELAY) {
            $this->getContainer()->get('mbh.fms.fms_export')->sendEmail($lastLogEntry, $currentDate);
        }
    }
}