<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Logs;


use Monolog\Handler\StreamHandler;

class LogCutter
{
    const STR_LENGTH = 20000000;

    /**@var StreamHandler */
    private $channelManagerHandler;

    /**@var int */
    private $fpPos;

    /**@return int */
    public function getFpPos()
    {
        return $this->fpPos;
    }

    /**@param int $fpPos */
    public function setFpPos(int $fpPos): void
    {
        $this->fpPos = $fpPos;
    }

    public function __construct(StreamHandler $logHandler)
    {
        $this->channelManagerHandler = $logHandler;
    }

    public function getOffset(int $page, int $offset): ?int
    {
        $eolOffset = $this->getLocalOffset($page, $offset);

        return $eolOffset === 0 ? null : $eolOffset + $offset;
    }

    public function clearLogFile()
    {
        $file = $this->channelManagerHandler->getUrl();

        if (file_exists($file) && is_readable($file)) {
            $file = $this->channelManagerHandler->getUrl();
            file_put_contents($file, '');
        }
    }

    private function getLocalOffset(int $page, int $offset): int
    {
        $eolOffset = 0;
        $file = $this->channelManagerHandler->getUrl();

        if (file_exists($file) && is_readable($file)) {
            $fp = fopen($file, 'r');
            fseek($fp, -self::STR_LENGTH * $page + $offset, SEEK_END);
            $content = fread($fp, self::STR_LENGTH);
            if (ftell($fp) !== self::STR_LENGTH) {
                $eolOffset = strpos($content, PHP_EOL) + 1;
            }
        }

        return $eolOffset;
    }

    public function getNext(int $page = 1, int $offset = 0): ?array
    {
        $data = [];

        $content = $this->cutFileSetFpPosition($page, $offset);
        if (!is_null($content)) {
            $content = $this->cutStr($content);
        }

        if (!is_null($content)) {
            $data = $this->formatData($content);
        }

        return $data === [] ? null : $data;
    }

    private function cutStr($content)
    {
        if ($this->getFpPos() === 0) {
            $eolOffsetFromEnd = strrpos($content, PHP_EOL);
            $content = substr($content, 0, $eolOffsetFromEnd + 1);
        } else {
            $eolOffset = strpos($content, PHP_EOL) + 1;
            $content = substr($content, $eolOffset);
        }

        return substr($content, 0, -1);
    }

    private function cutFileSetFpPosition(int $page = 1, int $offset = 0): ?string
    {
        $file = $this->channelManagerHandler->getUrl();

        if (file_exists($file) && is_readable($file)) {
            $fp = fopen($file, 'r');
            fseek($fp, -self::STR_LENGTH * $page + $offset, SEEK_END);
            $this->setFpPos(ftell($fp));
            $content = fread($fp, self::STR_LENGTH);
            var_dump($content);
            fclose($fp);
        }

        return (isset($content) && strlen($content)) ? $content : null;
    }

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

        foreach ($contentArray as $string) {
            $stringHalf = trim(preg_replace('!\s+!', ' ', mb_substr($string, 22)));
            $data[] = [
                trim(mb_substr($string, 0, 21)),
                $stringHalf
            ];
        }

        return $data ?? [];
    }
}
