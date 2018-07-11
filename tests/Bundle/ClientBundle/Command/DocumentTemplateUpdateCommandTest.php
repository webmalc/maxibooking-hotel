<?php
/**
 * Created by PhpStorm.
 * Date: 22.05.18
 */

namespace Bundle\ClientBundle\Command;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Test\Traits\FixturesTestTrait;
use MBH\Bundle\ClientBundle\Command\DocumentTemplateUpdateCommand;
use MBH\Bundle\ClientBundle\DataFixtures\MongoDB\DocumentTemplateData;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DocumentTemplateUpdateCommandTest extends KernelTestCase
{
    use FixturesTestTrait;

    public function setUp()
    {
        static::$kernel = static::createKernel();

        static::$kernel->boot();

        self::baseFixtures();
    }

    public function testExecuteNotEdited()
    {
        $needle = DocumentTemplateUpdateCommand::MSG_ATTENTION_UPDATED_TEMPLATES_DEFAULT_EXCEPT;

        $nameTemplates = array_keys(
            array_merge(
                DocumentTemplateData::DOCUMENT_TEMPLATE_DATA[DocumentTemplateData::LOCALE_RU]
            )
        );

        $randName = $nameTemplates[array_rand($nameTemplates)];

        $container = self::getContainerStat();

        /** @var DocumentManager $dm */
        $dm = $container->get('doctrine.odm.mongodb.document_manager');

        /** @var DocumentTemplate $template */
        $template = $dm
            ->getRepository('MBHClientBundle:DocumentTemplate')
            ->findOneBy([
                'title' => $randName
            ]);

        $template->setUpdatedBy('test');

        $dm->flush($template);

        $lastString = $this->runCommand();

        $this->assertTrue(
            strpos($lastString, $needle . $randName) !== false
        );
    }

    public function testExecuteAll()
    {
        $needle = DocumentTemplateUpdateCommand::MSG_OK_ALL_UPDATE;

        $lastString = $this->runCommand(true);

        $this->assertTrue(
            strpos($lastString, $needle) !== false
        );
    }

    /**
     * @param bool $all
     * @return string
     */
    private function runCommand(bool $all = false): string
    {
        $application = new Application(static::$kernel);

        $application->add(new DocumentTemplateUpdateCommand());


        $command = $application->find('mbh:document_template:update');

        $command->setApplication($application);


        $commandTester = new CommandTester($command);

        $input = [
            'command' => $command->getName(),
        ];

        if ($all) {
            $input['--all'] = true;
        }

        $commandTester->execute($input);

        $fileName = static::$kernel->getRootDir() . '/../var/clients/';
        $fileName .= static::$kernel->getEnvironment() . '/logs/';
        $fileName .= static::$kernel->getEnvironment() . '.' . DocumentTemplateUpdateCommand::FILE_LOG_NAME;
        $fileArr = explode(PHP_EOL, trim(file_get_contents($fileName)));

        return end($fileArr);
    }
}