<?php
/**
 * Created by PhpStorm.
 * Date: 17.05.18
 */

namespace MBH\Bundle\ClientBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DocumentTemplateUpdateCommand extends ContainerAwareCommand
{

    private $rootDir;
    private $isRootDirInit = false;

    private $dm;
    private $isDmInit = false;

    protected function configure()
    {
        $this
            ->setName('mbh:document_template:update')
            ->setDescription('Updating default document templates from files (all or not edited)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->removeTemplate($this->getTemplates());

        $command = 'doctrine:mongodb:fixtures:load --append --fixtures=' . $this->getRootDir();
        $command .= '/../src/MBH/Bundle/ClientBundle/DataFixtures/MongoDB/DocumentTemplateData.php';
        $this->runCommand($command);
    }

    private function getTemplates(bool $all = true)
    {
        $criteria = ['isDefault' => true];

        if (!$all) {
            $criteria['updatedBy'] = null;
        }

        return $this->getDM()->getRepository('MBHClientBundle:DocumentTemplate')->findBy($criteria);
    }

    private function removeTemplate($data)
    {
        $dm = $this->getDM();

        foreach ($data as $t) {
            $dm->remove($t);
        }

        $dm->flush();
    }

    private function runCommand(string $command)
    {
        $env = $this->getContainer()->get('kernel')->getEnvironment();
        $client = $this->getContainer()->getParameter('client');
        $process = new Process(
            'nohup php ' . $this->getRootDir() . '/../bin/console ' . $command . ' --no-debug --env=' . $env,
            null, [\AppKernel::CLIENT_VARIABLE => $client]
        );
        $process->run();
    }

    private function getDM()
    {
        if (!$this->isDmInit) {
            $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
            $this->isDmInit = true;
        }

        return $this->dm;
    }

    private function getRootDir()
    {
        if (!$this->isRootDirInit) {
            $this->rootDir = $this->getContainer()->get('kernel')->getRootDir();
            $this->isRootDirInit = true;
        }

        return $this->rootDir;
    }
}