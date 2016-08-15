<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/12/16
 * Time: 10:41 AM
 */

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;


class FormTranslateConverter extends AbstractTranslateConverter
{

    const SUFFIX = '.php';

    const FOLDER = '/Form';

    const TYPE = "FormParser";



    /**
     * @param string $string
     * @return mixed
     */
    protected function getConvertPattern(string $string)
    {
        return sprintf('%s', $string);
    }

    protected function transIdPattert(): string
    {
        return '%s.form.%s.%s';
    }

    protected function checkAdvanceConditions($line): bool
    {
        $words = [
            'label',
            'help',
            'placeholder'
        ];
        $pattern = '/' . implode('|', $words) . '/';
        if (preg_match($pattern, $line)) {
            return true;
        }
        return false;
    }


}