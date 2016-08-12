<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/12/16
 * Time: 10:41 AM
 */

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;


use Symfony\Bundle\FrameworkBundle\Translation\PhpExtractor;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FormTranslateConverter extends AbstractTranslateConverter
{

    const SUFFIX = '.php';

    const FOLDER = '/Form';


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
        return '%s.view.%s.%s';
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