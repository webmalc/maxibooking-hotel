<?php
/**
 * Date: 23.05.19
 */

namespace MBH\Bundle\OnlineBundle\Services\MBSite;


use MBH\Bundle\OnlineBundle\Lib\MBSite\StyleDataInterface;
use Symfony\Bridge\Monolog\Logger;

class StyleDataFromFile implements StyleDataInterface
{
    public const MSG_NO_FOUND_FILE = 'File "%s" not found in "%s".';
    public const MSG_NO_CONTENT = 'Can not read file "%s", path: %s.';

    private const TYPE_LOG_INFO = 'info';
    private const TYPE_LOG_ERROR = 'error';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $dir;

    public function __construct(Logger $logger, string $rootDir)
    {
        $this->logger = $logger;
        $this->dir = $rootDir . self::PREFIX_DIR;
    }

    public function getContent(string $fileName, string $formName): ?string
    {
        $pathToFile = $this->getDir() . $formName . '/' . $fileName;

        if (!file_exists($pathToFile)) {
            $this->logger(
                sprintf(
                    self::MSG_NO_FOUND_FILE,
                    $fileName,
                    $pathToFile
                ),
                self::TYPE_LOG_ERROR
            );

            return null;
        }

        $content = file_get_contents($pathToFile);

        if ($content === false) {
            $this->logger(sprintf(self::MSG_NO_CONTENT, $fileName ,$pathToFile), self::TYPE_LOG_ERROR);

            return null;
        }

        return $content;
    }

    private function getDir(): string
    {
        return $this->dir;
    }

    private function logger(string $msg, string $type = self::TYPE_LOG_INFO): void
    {
        switch ($type) {
            case self::TYPE_LOG_ERROR:
                $this->logger->addError($msg);
                return;
            case self::TYPE_LOG_INFO:
                $this->logger->addInfo($msg);
                return;
            default:
                $this->logger->addNotice($msg);
                return;
        }
    }
}
