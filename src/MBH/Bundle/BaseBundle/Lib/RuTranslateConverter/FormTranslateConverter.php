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

    const FILE_SUFFIX = '.php';

    const FOLDER = '/Form';

    const TYPE = "FormParser";

    const HANDLE_TYPE = 'form';


    /**
     * @param string $transliteratedString
     * @return mixed
     */
    protected function getConvertPattern(string $transliteratedString)
    {
        return sprintf('%s', $transliteratedString);
    }

    protected function transIdPattert(): string
    {
        return '%s.form.%s.%s';
    }

    protected function checkAdvanceConditions(string $line): bool
    {
        $words = ['label', 'help', 'placeholder'];
        $pattern = '/'.implode('|', $words).'/';
        if (preg_match($pattern, $line)) {
            return true;
        } else {
            return false;
        }


    }


}