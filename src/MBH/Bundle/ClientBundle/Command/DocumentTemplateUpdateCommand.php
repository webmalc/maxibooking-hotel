<?php
/**
 * Created by PhpStorm.
 * Date: 17.05.18
 */

namespace MBH\Bundle\ClientBundle\Command;


use \Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ClientBundle\DataFixtures\MongoDB\DocumentTemplateData;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentTemplateUpdateCommand extends ContainerAwareCommand
{
    public const COMMAND_NAME = 'mbh:document_template:update';

    /**
     * @var string
     */
    private $rootDir;
    /**
     * @var bool
     */
    private $isRootDirInit = false;

    /**
     * @var DocumentManager
     */
    private $dm;
    /**
     * @var bool
     */
    private $isDmInit = false;

    /**
     * @var array
     */
    private $defaultNameTemplates;
    /**
     * @var bool
     */
    private $isDefaultNameTemplatesInit = false;

    /**
     * @var
     */
    private $templates;

    /**
     * @var bool
     */
    private $isTemplatesInit = false;

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Updating default document templates from files (all or not edited)')
            ->addOption('all', 'a', null, 'update all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all') === true) {
            $this->updateAll('all');
        } else {
            $this->updateNotEdited();
        }
    }

    /**
     * Готовит сообщение при обновлении всех записей
     *
     * @param string|null $option
     */
    private function msgUpdateAll(string $option = null): void
    {
        $msg = 'OK. Update all templates.';

        if ($option !== null) {
            $msg .= ' with options "'. $option .'".';
        }

        $this->logger($msg);
    }

    /**
     * Готовит сообщение, со списком шаблонов которые не были обновленны
     */
    private function msgUnchanged(): void
    {
        $msg = 'ATTENTION. Updated templates default, except: ';
        $msg .= implode(
            ', ',
            array_diff(
                $this->getDefaultNameTemplates(),
                $this->getNotEditedNameTemplates()
            )
        );
        $msg .= '.';
        $this->logger($msg);
    }

    /**
     * Логирование (в файл)
     *
     * @param string $msg
     */
    private function logger(string $msg)
    {
//        $this->getContainer()->get('logger')->addInfo($msg);
        $client = $this->getContainer()->get('kernel')->getClient();
        $date = new \DateTime();
        $str = $date->format($date::ISO8601) . " {$client} \"{$msg}\"" . PHP_EOL;
        $fileName = $this->getRootDir() . '/../var/logs/document_template_update.log';

        file_put_contents($fileName, $str, FILE_APPEND);
    }

    /**
     * Обновление шаблонов, исключая измененные
     */
    private function updateNotEdited(): void
    {
        $t = $this->getTemplates(false);

        if (count($t) < count($this->getDefaultNameTemplates())) {
            $this->msgUnchanged();
        } else {
            $this->msgUpdateAll();
        }

        $this->updateTemplates($t);
    }

    /**
     * Обновление всех шаблонов
     *
     * @param string|null $option
     */
    private function updateAll(string $option = null): void
    {
        $t = $this->getTemplates();
        $this->updateTemplates($t);

        $this->msgUpdateAll($option);
    }

    /**
     * @param bool $all
     * @return DocumentTemplate[]
     */
    private function getTemplates(bool $all = true): array
    {
        if (!$this->isTemplatesInit) {
            $criteria = [];
            $criteria['title'] = [
                '$in' => $this->getDefaultNameTemplates(),
            ];

            if (!$all) {
                $criteria['updatedBy'] = null;
            }

            $this->templates = $this->getDM()->getRepository('MBHClientBundle:DocumentTemplate')->findBy($criteria);
            $this->isTemplatesInit = true;
        }

        return $this->templates;
    }

    /**
     * @param $data
     */
    private function updateTemplates($data): void
    {
        $dm = $this->getDM();

        $defaultData = $this->getDefaultTemplatesData();
        /** @var DocumentTemplate $template */
        foreach ($data as $template) {
            $filePath = $this->getRootDir()
                . '/../src/MBH/Bundle/PackageBundle/Resources/views/Documents/pdfTemplates/'
                . $defaultData[$template->getTitle()]
                . '.html.twig';

            $content = file_get_contents($filePath);
            $template->setContent($content);
            $dm->persist($template);
        }

        $dm->flush();
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    private function getDM()
    {
        if (!$this->isDmInit) {
            $this->dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
            $this->isDmInit = true;
        }

        return $this->dm;
    }

    /**
     * @return string
     */
    private function getRootDir(): string
    {
        if (!$this->isRootDirInit) {
            $this->rootDir = $this->getContainer()->get('kernel')->getRootDir();
            $this->isRootDirInit = true;
        }

        return $this->rootDir;
    }

    /**
     * @return array
     */
    private function getDefaultTemplatesData(): array
    {
        $locale = $this->getContainer()->getParameter('locale') === 'ru' ? 'ru' : 'com';

        return DocumentTemplateData::DOCUMENT_TEMPLATE_DATA[$locale];
    }


    /**
     * @return array
     */
    private function getDefaultNameTemplates(): array
    {
        if (!$this->isDefaultNameTemplatesInit) {

            $this->defaultNameTemplates = array_keys($this->getDefaultTemplatesData());
            $this->isDefaultNameTemplatesInit = true;
        }

        return $this->defaultNameTemplates;
    }

    /**
     * Имена шаблонов (без изменений)
     *
     * @return array
     */
    private function getNotEditedNameTemplates(): array
    {
        return array_map(
            function ($entity) {
                /** @var DocumentTemplate $entity */
                return $entity->getTitle();
            },
            $this->getTemplates(false)
        );
    }
}