<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Logs;


use Monolog\Handler\StreamHandler;

class ChannelManagerLogHandler
{
    const STR_LENGTH = 1000000;

    /**@var StreamHandler */
    private $channelManagerHandler;

    /**
     * LogCutter constructor.
     * @param StreamHandler $logHandler
     */
    public function __construct(StreamHandler $logHandler)
    {
        $this->channelManagerHandler = $logHandler;
    }

    /**
     * @param int $page
     * @return string|null
     */
    private function cutFile(int $page = 1): ?string
    {
        $file = $this->channelManagerHandler->getUrl();

        if (file_exists($file) && is_readable($file)) {
            $fp = fopen($file, 'r');
            if (filesize($file) === 0) {
                return null;
            } elseif (filesize($file) < self::STR_LENGTH) {
                $content = fread($fp, filesize($file));
            } else {
                fseek($fp, -self::STR_LENGTH * $page, SEEK_END);
                $content = fread($fp, self::STR_LENGTH);
            }

            fclose($fp);
        }

        return (isset($content) && strlen($content)) ? $content : null;
    }

    /**
     * @param int $page
     * @return array|null
     */
    public function getNext(int $page = 1): ?array
    {
        $content = $this->cutFile($page);

        $data = [];
        if (!is_null($content)) {
            $data = $this->formatData($content);
        }

        return $data === [] ? null : $data;
    }

    /**
     * @param string $content
     * @return array
     */
    private function formatData(string $content): array
    {
        $contentArray = explode(
            PHP_EOL,
            htmlentities(
                implode("\n", array_reverse(explode("\n", $content))),
                ENT_SUBSTITUTE,
                "UTF-8"
            )
        );

        return $contentArray ?? [];
    }

    /**
     * @param int $page
     * @return array|null
     */
    public function getPaginationArray(int $page): ?array
    {
        $length = $this->getPaginationLength();

        if (($page < 1) || ($page > $length) || ($length <= 1)) {
            return null;
        }

        if ($length <= 5) {
            for ($i = 1; $i <= $length; ++$i) {
                $arr[] = (string)$i;
            }
            return $arr ?? null;
        }

        switch ($page) {
            case 1:
            case 2:
            case 3:
                $arr = ['1', '2', '3', '4', '...', (string)$length];
                break;
            case $length:
            case $length - 1:
            case $length - 2:
                $arr = ['1', '...', (string)($length - 3), (string)($length - 2), (string)($length - 1), (string)($length)];
                break;
            default:
                $arr = ['1', '...', (string)($page - 1), (string)($page), (string)($page + 1), '...', (string)$length];
                break;
        }

        return $arr;
    }

    /**
     * @return int|null
     */
    private function getPaginationLength(): ?int
    {
        $file = $this->channelManagerHandler->getUrl();

        if (file_exists($file) && is_readable($file)) {
            return (int)ceil(filesize($file) / self::STR_LENGTH);
        }

        return null;
    }

    public function clearLogFile()
    {
        $file = $this->channelManagerHandler->getUrl();

        if (file_exists($file) && is_readable($file)) {
            $file = $this->channelManagerHandler->getUrl();
            file_put_contents($file, '');
        }
    }
}
