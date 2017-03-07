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
    const SUFFIX = '.php';

    const FOLDER = '/Document';

    const TYPE = "DocParser";

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
        return '%s.document.%s.%s';
    }

    protected function checkAdvanceConditions($line): bool
    {
        $words = [
            'message'
        ];
        $pattern = '/' . implode('|', $words) . '/';
        if (preg_match($pattern, $line)) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    protected function domainChecker()
    {
        return 'validators';
    }


}