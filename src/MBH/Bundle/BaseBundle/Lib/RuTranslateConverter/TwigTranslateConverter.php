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


    const SUFFIX = '.html.twig';

    const FOLDER = '/Resources/views';

    const TYPE = "TwigParser";

    /**
     * @param $string
     * @return mixed
     */
    protected function getConvertPattern(string $string)
    {
        return sprintf('{{ \'%s\'|trans }} ', $string);
    }


    protected function transIdPattert(): string
    {
        return '%s.view.%s.%s';
    }


}