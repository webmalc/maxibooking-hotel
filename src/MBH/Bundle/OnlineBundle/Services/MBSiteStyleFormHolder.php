<?php
/**
 * Date: 07.05.19
 */

namespace MBH\Bundle\OnlineBundle\Services;


use Symfony\Bridge\Monolog\Logger;

class MBSiteStyleFormHolder
{
    private const FILE_PREFIX = '/../src/MBH/Bundle/OnlineBundle/Resources/public/css/api/search-form/css-for-mb-site/';

    private const FILE_STYLE_SEARCH_IFRAME = 'search-form.css';
    private const FILE_STYLE_CALENDAR_IFRAME = 'calendar.css';
    private const FILE_STYLE_ADDITIONAL_IFRAME = 'additional-form.css';
    private const FILE_STYLE_RESULT_FORM = '';

    public const MSG_NO_FOUND_FILE = 'File "%s" not found in "%s".';
    public const MSG_NO_CONTENT = 'Can not read file "%s".';

    private const TYPE_LOG_INFO = 'info';
    private const TYPE_LOG_ERROR = 'error';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $rootDir;

    public function __construct(Logger $logger, string $rootDir)
    {
        $this->logger = $logger;
        $this->rootDir = $rootDir;
    }

    public function getStyleSearchForm(): ?string
    {
        return $this->getFileContent(self::FILE_STYLE_SEARCH_IFRAME);
    }

    public function getStyleCalendar(): ?string
    {
        return $this->getFileContent(self::FILE_STYLE_CALENDAR_IFRAME);
    }

    public function getStyleAdditionalForm(): ?string
    {
        return $this->getFileContent(self::FILE_STYLE_ADDITIONAL_IFRAME);
    }

    private function getRootDir(): string
    {
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
        switch ($type) {
            case self::TYPE_LOG_INFO:
                $this->logger->addInfo($msg);
                break;
            case self::TYPE_LOG_ERROR:
                $this->logger->addError($msg);
                break;
            default:
                $this->logger->addNotice($msg);
        }
    }
}
