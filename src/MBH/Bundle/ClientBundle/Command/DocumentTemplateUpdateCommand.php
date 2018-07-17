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

    public const FILE_LOG_NAME = 'document_template_update.log';

    public const MSG_OK_ALL_UPDATE = 'OK. Update all templates.';

    public const MSG_ATTENTION_NOT_FOUND_DEFAULT_TEMPLATES = 'ATTENTION. Not found default templates.';

    public const MSG_ATTENTION_UPDATED_TEMPLATES_DEFAULT_EXCEPT = 'ATTENTION. Updated templates default, except: ';

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
        $all = $input->getOption('all') === true;

        if ($all) {
            $t = $this->getTemplates();
        } else {
            $t = $this->getTemplates(false);
        }

        if ($t === []) {
            $msg = $this->msgNotFoundTemplates();
        } else {
            if ($all) {
                $msg = $this->updateAll($t, 'all');
            } else {
                $msg = $this->updateNotEdited($t);
            }
        }

        $this->logger($msg);
    }

    /**
     * Готовит сообщение при обновлении всех записей
     *
     * @param string|null $option
     * @return string
     */
    private function msgUpdateAll(string $option = null): string
    {
        $msg = self::MSG_OK_ALL_UPDATE;

        if ($option !== null) {
            $msg .= ' with option: ' . $option . '.';
        }

        return $msg;
    }

    /**
     * @return string
     */
    private function msgNotFoundTemplates(): string
    {
        return self::MSG_ATTENTION_NOT_FOUND_DEFAULT_TEMPLATES;
    }

    /**
     * Готовит сообщение, со списком шаблонов которые не были обновленны
     *
     * @return string
     */
    private function msgUnchanged(): string
    {
        $msg = self::MSG_ATTENTION_UPDATED_TEMPLATES_DEFAULT_EXCEPT;
        $msg .= implode(
            ', ',
            array_diff(
                $this->getDefaultNameTemplates(),
                $this->getNotEditedNameTemplates()
            )
        );
        $msg .= '.';

        return $msg;
    }

    /**
     * @param string $msg
     */
    private function logger(string $msg)
    {
        $this->getContainer()->get('mbh.document_template_update.logger')
            ->addInfo($msg);
    }

    /**
     * Обновление шаблонов, исключая измененные
     *
     * @param DocumentTemplate[] $templates
     *
     * @return string
     */
    private function updateNotEdited(array $templates): string
    {
        $this->updateTemplates($templates);

        if (count($templates) < count($this->getDefaultNameTemplates())) {
            return $this->msgUnchanged();
        }

        return $this->msgUpdateAll();
    }

    /**
     * Обновление всех шаблонов
     *
     * @param DocumentTemplate[] $templates
     * @param string|null $option
     *
     * @return string
     */
    private function updateAll(array $templates, string $option = null): string
    {
        $this->updateTemplates($templates);

        return $this->msgUpdateAll($option);
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
        $locale = $this->getContainer()->getParameter('locale') === DocumentTemplateData::LOCALE_RU
            ? DocumentTemplateData::LOCALE_RU
            : DocumentTemplateData::LOCALE_COM;

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