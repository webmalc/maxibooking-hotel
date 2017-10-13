<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/11/16
 * Time: 5:09 PM
 */

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;


/**
 * Class TwigTranslateConverter
 * @package MBH\Bundle\BaseBundle\Lib\RuTranslateConverter
 */
class TwigTranslateConverter extends AbstractTranslateConverter
{


    const FILE_SUFFIX = '.html.twig';

    const FOLDER = '/Resources/views';

    const TYPE = "TwigParser";

    const HANDLE_TYPE = 'twig';

    /**
     * @param $transliteratedString
     * @return mixed
     */
    protected function getConvertPattern(string $transliteratedString)
    {
        return sprintf('{{ \'%s\'|trans }}', $transliteratedString);
    }


    protected function transIdPattert(): string
    {
        return '%s.view.%s.%s';
    }

    protected function checkAdvanceConditions(string $line): bool
    {
        return true;
    }


}