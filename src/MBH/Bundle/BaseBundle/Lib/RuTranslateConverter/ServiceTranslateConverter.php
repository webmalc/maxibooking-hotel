<?php namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;

/**
 * Class ServiceTranslateConverter
 * @package MBH\Bundle\BaseBundle\Lib\RuTranslateConverter
 */
class ServiceTranslateConverter extends AbstractTranslateConverter
{


    const FILE_SUFFIX = '.php';

    const FOLDER = '/Service';

    const TYPE = "ServiceParser";

    const HANDLE_TYPE = 'service';

    /**
     * @param $transliteratedString
     * @return mixed
     */
    protected function getConvertPattern(string $transliteratedString)
    {
        return sprintf('%s', $transliteratedString);
    }


    protected function transIdPattert(): string
    {
        return '%s.service.%s.%s';
    }

    protected function checkAdvanceConditions(string $line): bool
    {
        return true;
    }


}