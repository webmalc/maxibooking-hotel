<?php namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;

/**
 * Class ServiceTranslateConverter
 * @package MBH\Bundle\BaseBundle\Lib\RuTranslateConverter
 */
class ServicesTranslateConverter extends AbstractTranslateConverter
{


    const FILE_SUFFIX = '.php';

    const FOLDER = '/Services';

    const TYPE = "ServicesParser";

    const HANDLE_TYPE = 'services';

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
        return '%s.services.%s.%s';
    }

    protected function checkAdvanceConditions(string $line): bool
    {
        return true;
    }


}