<?php

namespace MBH\Bundle\VegaBundle\Command;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\VegaBundle\Document\VegaDocumentType;
use MBH\Bundle\VegaBundle\Document\VegaFMS;
use MBH\Bundle\VegaBundle\Document\VegaRegion;
use MBH\Bundle\VegaBundle\Document\VegaState;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MBH\Bundle\VegaBundle\Service\FriendlyFormatter;


/**
 * Class VegaImportCommand
 * @package MBH\Bundle\BaseBundle\Command

 */
class VegaImportCommand extends ContainerAwareCommand
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var ProgressBar
     */
    protected $progress;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output); // TODO: Change the autogenerated stub

        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
    }


    protected function configure()
    {
        $this
            ->setName('mbh:vega:import')
            ->setDescription('Import vega catalogs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->progress = new ProgressBar($output);

        $output->writeln("Import FMS");
        $this->importFMS();
        $output->writeln("\nImport State");
        $this->importState();
        $output->writeln("\nImport Region");
        $this->importRegion();

        $output->writeln("\nImport Document Type");
        $this->importDocumentType();

        $output->writeln("\nDone");
    }

    /**
     * @return string
     */
    private function getResourcesFolderName()
    {
        /** @var \AppKernel $kernel */
        $kernel = $this->getContainer()->get('kernel');
        $root = $kernel->getBundle('MBHVegaBundle')->getPath();
        //$root = $kernel->getRootDir();
        return $root.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'data';
    }


    private function importFMS()
    {
        $this->dm->getRepository('MBHVegaBundle:VegaFMS')->createQueryBuilder()->remove()->getQuery()->execute();
        $filePath = $this->getResourcesFolderName().DIRECTORY_SEPARATOR.'dict_s_fms.csv';
        $this->progress->start(8645);

        if($resource = fopen($filePath, 'r'))
        {
            fgetcsv($resource, 1000, ';');
            while($data = fgetcsv($resource, 1000, ";")){
                $line = reset($data);
                $line = iconv('windows-1251', 'UTF-8', $line);
                $data = [];
                preg_match_all("/(.+)\s{4}(.+)/", $line, $data);
                if(count($data) == 3){
                    $data = array_map('reset', $data);
                    $data = array_map('trim', $data);
                    if($data[1] && $data[2]){
                        $fms = new VegaFMS();
                        $fms->setCode($data[2]);
                        $fms->setOriginalName($data[1]);
                        $fms->setName(FriendlyFormatter::convertFMS($data[1]));
                        $this->dm->persist($fms);

                        $this->progress->advance();
                    }
                };
            }
        }

        $this->dm->flush();
        $this->progress->advance();

        $this->progress->finish();
    }


    private function importState()
    {
        $this->dm->getRepository('MBHVegaBundle:VegaState')->createQueryBuilder()->remove()->getQuery()->execute();
        $filePath = $this->getResourcesFolderName().DIRECTORY_SEPARATOR.'dict_s_state.csv';
        $this->progress->start(285);

        if($resource = fopen($filePath, 'r'))
        {
            fgetcsv($resource, 1000, ';');
            while($data = fgetcsv($resource, 1000, ";")){
                $line = reset($data);
                $line = trim(iconv('windows-1251', 'UTF-8', $line));
                if($line) {
                    $fms = new VegaState();
                    $fms->setOriginalName($line);
                    $fms->setName(FriendlyFormatter::convertCountry($line));

                    $this->dm->persist($fms);
                    $this->progress->advance();
                }
            }
        }

        $this->dm->flush();
        $this->progress->advance();

        $this->progress->finish();
    }


    private function importRegion()
    {
        $this->dm->getRepository('MBHVegaBundle:VegaRegion')->createQueryBuilder()->remove()->getQuery()->execute();
        $filePath = $this->getResourcesFolderName().DIRECTORY_SEPARATOR.'dict_region.csv';
        $this->progress->start(87);

        if($resource = fopen($filePath, 'r'))
        {
            fgetcsv($resource, 1000, ';');
            while($data = fgetcsv($resource, 1000, ";")){
                $line = reset($data);
                $line = trim(iconv('windows-1251', 'UTF-8', $line));
                if($line) {
                    $fms = new VegaRegion();
                    $fms->setOriginalName($line);
                    $fms->setName(FriendlyFormatter::convertRegion($line));

                    $this->dm->persist($fms);
                    $this->progress->advance();
                }
            }
        }

        $this->dm->flush();

        $this->progress->advance();
        $this->progress->finish();
    }


    public function importDocumentType()
    {
        $this->dm->getRepository('MBHVegaBundle:VegaDocumentType')->createQueryBuilder()->remove()->getQuery()->execute();
        $documentTypes = $this->getContainer()->get('mbh.vega.dictionary_provider')->getDocumentTypes();

        $this->progress->start(count($documentTypes) + 1);

        foreach($documentTypes as $code => $name){
            $documentType = new VegaDocumentType();
            $documentType
                ->setCode($code)
                ->setOriginalName($name)
                ->setName(FriendlyFormatter::convertDocumentType($name));

            $this->dm->persist($documentType);
            $this->progress->advance();
        }
        $this->dm->flush();

        $this->progress->advance();
        $this->progress->finish();
    }
}