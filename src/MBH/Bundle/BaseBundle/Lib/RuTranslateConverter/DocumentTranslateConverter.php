<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/12/16
 * Time: 5:52 PM
 */

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;


class DocumentTranslateConverter extends AbstractTranslateConverter
{
    const FILE_SUFFIX = '.php';

    const FOLDER = '/Document';

    const TYPE = "DocParser";

    const TRANSLATE_TYPE = '';

    const HANDLE_TYPE = 'doc';

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
        return '%s.document.%s.%s';
    }

    protected function checkAdvanceConditions(string $line): bool
    {
        $words = ['message'];
        $pattern = '/' . implode('|', $words) . '/';
        if (preg_match($pattern, $line)) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    protected function getTranslationDomain()
    {
        return 'validators';
    }

}