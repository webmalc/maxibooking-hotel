<?php

namespace MBH\Bundle\FMSBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\FMSBundle\Document\KonturDocumentType;
use MBH\Bundle\FMSBundle\Document\KonturFMSOrgan;
use MBH\Bundle\FMSBundle\Document\KonturProfessionType;
use MBH\Bundle\FMSBundle\Document\OKSMCountry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FMSGetDictionarysCommand extends ContainerAwareCommand
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var ProgressBar
     */
    protected $progressBar;

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setName('mbh:oksm:load')
            ->setDescription('Loading countries in the database')
        ;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::__construct();
        $this->dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager')->getManager();
        $this->basePath = $this->getContainer()->get('kernel')->getRootDir() . '/../src/MBH/Bundle/FMSBundle/Resources/data/';
    }

    private function importDocumentTypes()
    {
        $this->dm->getRepository('MBHFMSBundle:KonturDocumentType')->createQueryBuilder()->remove()->getQuery()->execute();

        $documentTypesFile = fopen($this->basePath.'dict_documenttype.csv', 'r');
        while (($row = fgetcsv($documentTypesFile, 1000, ';')) !== false)
        {
            if (!is_numeric($row[0])) {
                continue;
            }
            $documentType = new KonturDocumentType();
            $documentType->setId($row[0]);

            if (empty($row[1])) {
                throw new Exception("Не указано значение названия для типа документа с ID = {$documentType->getId()}");
            }
            $documentType->setName($row[1]);

            if (empty($row[2])) {
                throw new Exception("Не указано значение актуальности для типа документа с ID = {$documentType->getActualityStatus()}");
            }
            $documentType->setActualityStatus($row[2]);
            $this->dm->persist($documentType);
        }
        $this->progressBar->advance();
        $this->progressBar->finish();
    }

    private function importOfficialFMSOrgans()
    {
        $this->dm->getRepository('MBHFMSBundle:KonturOfficialFMSOrgan')->createQueryBuilder()->remove()->getQuery()->execute();

        $fmsOrgansFile = fopen($this->basePath.'dict_officialorgan_fms.csv', 'r');

        while(($row = fgetcsv($fmsOrgansFile, 1000, ';')) !== false)
        {
            if (!is_numeric($row[0])) {
                continue;
            }
            $fmsOrgan = new KonturFMSOrgan();
            $fmsOrgan->setId($row[0]);

            if (empty($row[1])) {
                throw new Exception("Не указано значение названия органа ФМС с ID = {$fmsOrgan->getId()}");
            }
            $fmsOrgan->setName($row[1]);

            if (empty($row[2])) {
                throw new Exception("Не указано значение кода органа ФМС с ID = {$fmsOrgan->getId()}");
            }

            if (empty($row[3])) {
                continue;
            }
            $date = \DateTime::createFromFormat('Y-m-d h:i:s.u', $row[3]);
            $errors = \DateTime::getLastErrors();
            if (count($errors['errors']) > 0) {
                throw new Exception("Указано некорректное значение даты окончания для органа ФМС с ID = {$fmsOrgan->getId()}");
            }
            $fmsOrgan->setEndDate($date);
            $this->dm->persist($fmsOrgan);
        }
        $this->progressBar->advance();
        $this->progressBar->finish();
    }

    private function importProfessionType()
    {
        $this->dm->getRepository('MBHFMSBundle:KonturProfessionType')->createQueryBuilder()->remove()->getQuery()->execute();

        $professionTypesFile = fopen($this->basePath.'dict_profession.csv', 'r');

        while (($row = fgetcsv($professionTypesFile, 1000, ';')) !== false)
        {
            if (!is_numeric($row[0])) {
                continue;
            }
            $professionType = new KonturProfessionType();
            $professionType->setId($row[0]);

            if (empty($row[1])) {
                throw new Exception("Не указано значение названия профессии с ID = {$professionType->getId()}");
            }
            $professionType->setName($row[1]);

            if (empty($row[2])) {
                throw new Exception("Не указано значение актуальности для профессии с ID = {$professionType->getId()}");
            }
            $professionType->setActualityStatus($row[2]);

            $this->dm->persist($professionType);
        }
        $this->dm->flush();

        $this->progressBar->advance();
        $this->progressBar->finish();
    }

    private function importOKSM()
    {
        $this->dm->getRepository('MBHFMSBundle:OKSMCountry')->createQueryBuilder()->remove()->getQuery()->execute();

        $oksmFile = fopen($this->basePath.'oksm.csv', 'r');

        while (($row = fgetcsv($oksmFile, 1000, ';')) !== false ) {
            if (!is_numeric($row[0])) {
                continue;
            }
            $country = new OKSMCountry();
            $country->setDigitalCode($row[0]);

            if (empty($row[1])) {
                throw new \Exception("Не указано значение краткого наименования страны с цифровым кодом " . $row[0]);
            }
            $country->setShortName($row[1]);

            if (empty($row[2])) {
                $fullName = mb_strtolower($row[1]);
                $country->setFullName(mb_convert_case($fullName, MB_CASE_TITLE, 'UTF-8'));
            }

            if (empty($row[3]) || !is_numeric($row[3])) {
                throw new Exception("Для страны с цифровым кодом '{$country->getDigitalCode()}' указано некорректное значение альфа-2 буквенного кода");
            }
            $country->setAlpha2Code($row[3]);

            if (empty($row[4]) || !is_numeric($row[4])) {
                throw new Exception("Для страны с цифровым кодом '{$country->getDigitalCode()}' указано некорректное значение альфа-2 буквенного кода");
            }
            $country->setAlpha3Code($row[4]);
            $this->dm->persist($country);
        }
        $this->dm->flush();

        $this->progressBar->advance();
        $this->progressBar->finish();
    }
}