<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/11/16
 * Time: 5:09 PM
 */

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;


use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class TwigTranslateConverter
 * @package MBH\Bundle\BaseBundle\Lib\RuTranslateConverter
 */
class TwigTranslateConverter extends AbstractTranslateConverter
{

    /**
     * @param bool $emulate
     * @return mixed|void
     */
    protected function execute($emulate = true)
    {
        $finder = new Finder();
        $pathPatterns = $this->getPathPatterns();
        $files = $finder->files()->name($pathPatterns['filesPattern'])->in($pathPatterns['directory']);

        foreach ($files as $file) {

            $changed = false;
            $contents = $file->getContents();
            $lines = explode("\n", $contents);

            foreach ($lines as &$line) {
                preg_match(self::RU_PATTERN, $line, $mathes);
                if ($mathes) {
                    $matchedOrigText = $mathes[0];
                    $messageId = $this->getTransIdPattern($file,  $matchedOrigText);
                    $convertPattern = $this->getConvertPattern($messageId);
                    $resultLine = str_replace($matchedOrigText, $convertPattern, $line);
                    $this
                        ->addMessage('Текущий файл ' . $file->getPathname())
                        ->addMessage('Исходная строка ' . $line)
                        ->addMessage('Найден текст ' . $matchedOrigText)
                        ->addMessage('Результирующая строка ' . $resultLine);
                    if (!$emulate && $this->helper && $this->helper->ask($this->input, $this->output,$this->question)) {
                        $changed = true;
                        $line = $resultLine;
                        $this->addMessage('Внесены изменения');
                        $this->catalogue->add([$messageId=>$matchedOrigText], $this->domainChecker());
                    }
                }

                if ($changed) {
                    $newfile = implode("\n", $lines);
                    $this->saveChangesToFile($file->getPathname(), $newfile);
                }

            }


        }
        $this->saveTranslationMessages();
        $this->addMessage('Done without errors');
    }

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