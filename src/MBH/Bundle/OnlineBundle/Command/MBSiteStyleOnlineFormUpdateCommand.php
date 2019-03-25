<?php
/**
 * Date: 25.03.19
 */

namespace MBH\Bundle\OnlineBundle\Command;


use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MBSiteStyleOnlineFormUpdateCommand extends ContainerAwareCommand
{
    public const COMMAND_NAME = 'mbh:mb_site:style_online_form:update';

    public const FILE_LOG_NAME = 'mb_site.log';

    public const OPT_NAME_ALL_IFRAME  = 'all';
    public const OPT_NAME_SEARCH_IFRAME  = 'search';
    public const OPT_NAME_CALENDAR_IFRAME  = 'calendar';
    public const OPT_NAME_ADDITIONAL_IFRAME  = 'additional';

    public const MSG_NO_OPTIONS = 'You must specify the min one frame.';
    public const MSG_NO_FOUND_CONFIG = 'Not found form online config for mb site.';
    public const MSG_NO_FOUND_FILE = 'File "%s" not found in "%s".';
    public const MSG_NO_CONTENT = 'Can not read file "%s".';
    public const MSG_ERROR_SEE_LOG = 'Error. See log file.';
    public const MSG_UPDATE_OK = 'Update with arguments: %s.';

    private const FILE_PREFIX = '/../src/MBH/Bundle/OnlineBundle/Resources/public/css/api/search-form/css-for-mb-site/';

    private const FILE_STYLE_SEARCH_IFRAME = 'search-form.css';
    private const FILE_STYLE_CALENDAR_IFRAME = 'calendar.css';
    private const FILE_STYLE_ADDITIONAL_IFRAME = 'additional-form.css';
    private const FILE_STYLE_RESULT_FORM = '';

    private const TYPE_LOG_INFO = 'info';
    private const TYPE_LOG_ERROR = 'error';

    /**
     * @var string
     */
    private $rootDir;

    protected function configure()
    {
        $generateDescription = function (string $optionName): string
        {
            $format = 'Update style for %s iframe.';
            return sprintf($format, $optionName);
        };

        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Update style for online form.')
            ->addOption(
                self::OPT_NAME_SEARCH_IFRAME,
                null,
                InputOption::VALUE_NONE,
                $generateDescription(self::OPT_NAME_SEARCH_IFRAME)
            )
            ->addOption(
                self::OPT_NAME_CALENDAR_IFRAME,
                null,
                InputOption::VALUE_NONE,
                $generateDescription(self::OPT_NAME_CALENDAR_IFRAME)
            )
            ->addOption(
                self::OPT_NAME_ADDITIONAL_IFRAME,
                null,
                InputOption::VALUE_NONE,
                $generateDescription(self::OPT_NAME_ADDITIONAL_IFRAME)
            )
            ->addOption(
                self::OPT_NAME_ALL_IFRAME,
                null,
                InputOption::VALUE_NONE,
                $generateDescription(self::OPT_NAME_ALL_IFRAME)
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        /** @var FormConfig $formConfig */
        $formConfig = $dm->getRepository(FormConfig::class)->getForMBSite();

        if ($formConfig === null) {
            $output->writeln(self::MSG_NO_FOUND_CONFIG);
            $this->logger(self::MSG_NO_FOUND_CONFIG);

            return 1;
        }

        $update = false;
        $updateAll = $input->getOption(self::OPT_NAME_ALL_IFRAME);

        if ($updateAll || $input->getOption(self::OPT_NAME_SEARCH_IFRAME)) {
            $content = $this->getFileContent(self::FILE_STYLE_SEARCH_IFRAME);
            if ($content === null) {
                $output->writeln(self::MSG_ERROR_SEE_LOG);

                return 1;
            }

            $formConfig->setCss($content);
            $update = true;
        }

        if ($updateAll || $input->getOption(self::OPT_NAME_CALENDAR_IFRAME)) {
            $content = $this->getFileContent(self::FILE_STYLE_CALENDAR_IFRAME);
            if ($content === null) {
                $output->writeln(self::MSG_ERROR_SEE_LOG);

                return 1;
            }

            $formConfig->setCalendarCss($content);
            $update = true;
        }

        if ($updateAll || $input->getOption(self::OPT_NAME_ADDITIONAL_IFRAME)) {
            $content = $this->getFileContent(self::FILE_STYLE_ADDITIONAL_IFRAME);
            if ($content === null) {
                $output->writeln(self::MSG_ERROR_SEE_LOG);

                return 1;
            }

            $formConfig->setAdditionalFormCss($content);
            $update = true;
        }

        if (!$update) {
            $output->writeln(self::MSG_NO_OPTIONS);
            $this->logger(self::MSG_NO_OPTIONS);

            return 1;
        }

        $dm->persist($formConfig);
        $dm->flush();

        $msg = sprintf(self::MSG_UPDATE_OK, json_encode($input->getOptions()));
        $output->writeln($msg);
        $this->logger($msg);

        return 0;
    }

    private function getRootDir(): string
    {
        if ($this->rootDir === null) {
            $this->rootDir = $this->getContainer()->get('kernel')->getRootDir();
        }

        return $this->rootDir;
    }

    private function getFileContent(string $file): ?string
    {
        $prefix = $this->getRootDir() . self::FILE_PREFIX;
        $pathToFile = $prefix . $file;

        if (!file_exists($pathToFile)) {
            $this->logger(sprintf(self::MSG_NO_FOUND_FILE, $file, $prefix), self::TYPE_LOG_ERROR);

            return null;
        }

        $content = file_get_contents($pathToFile);

        if ($content === false) {
            $this->logger(sprintf(self::MSG_NO_CONTENT, $pathToFile), self::TYPE_LOG_ERROR);

            return null;
        }

        return $content;
    }

    private function logger(string $msg, string $type = self::TYPE_LOG_INFO): void
    {
        $logger = $this->getContainer()->get('mbh.mb_site.logger');

        switch ($type) {
            case self::TYPE_LOG_INFO:
                $logger->addInfo($msg);
                break;
            case self::TYPE_LOG_ERROR:
                $logger->addError($msg);
                break;
            default:
                $logger->addNotice($msg);
        }
    }

}