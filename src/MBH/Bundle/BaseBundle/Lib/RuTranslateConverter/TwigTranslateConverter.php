<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/11/16
 * Time: 5:09 PM
 */

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;


use Symfony\Component\Finder\SplFileInfo;

class TwigTranslateConverter extends AbstractTranslateConverter
{
    /**
     * @return array
     */
    protected function getPathPatterns(): array
    {
        return [
            'filesPattern' => '*.twig',
            'directory' => $this->bundle->getPath().'/Resources/views'
        ];
    }

    /**
     * @param $string
     * @return mixed
     */
    protected function getConvertPattern(string $string)
    {
        return sprintf('{{ \'%s\'|trans }} ', $string);
    }

    /**
     * @param SplFileInfo $file
     * @return string
     */
    protected function getTransIdPattern(SplFileInfo $file, string $matchedOrigText): string
    {
        $transliterator = \Transliterator::create('Russian-Latin/BGN');

        $label = $transliterator->transliterate(str_replace(' ', '', $matchedOrigText));
        $bundleName = $this->bundle->getName();
        $dir = str_replace('.html.twig', '', $file->getRelativePathname());
        $dir = str_replace('/', '.', $dir);
        $transIdPattern = $bundleName . '.view.'.$dir.'.'.$label;
        return strtolower($transIdPattern);
    }


}