<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/11/16
 * Time: 4:57 PM
 */

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;


use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Translation\MessageCatalogue;


abstract class AbstractTranslateConverter implements RuTranslateInterface
{


    const TRANSLATE_FOLDER = '/Resources/translations';

//    const RU_PATTERN = '/([А-Яа-яЁё]+,?.?\??\-?\s*)+/u';
    const RU_PATTERN = '/([А-Яа-яЁё]+\s*[\-\.\?\,]?\s*)+/u';

    const TYPE = "AbstractParser";

    protected $bundle;


    protected $output;

    protected $container;


    protected $input;


    protected $helper;


    protected $question;

    protected $catalogue;


    public function __construct(BundleInterface $bundle, InputInterface $input, OutputInterface $output, ContainerInterface $container)
    {
        $this->bundle = $bundle;
        $this->output = $output;
        $this->container = $container;
        $this->input = $input;
        $this->catalogue = new MessageCatalogue('ru');
    }


    public function convert(QuestionHelper $helper)
    {
        $this->helper = $helper;
        $this->question = new ConfirmationQuestion('Применить изменения? ', false);
        $this->execute(false);
    }

    public function findEntry()
    {
        $this->execute();
    }





    protected function execute($emulate = true)
    {
        $files = $this->getFiles();
        if (!$files) {
            $this->addMessage('Пропускаем, нет директории '.static::FOLDER.' в текущем бандле ', $this->bundle->getName());
            return true;
        }
        foreach ($files as $file) {

            $changed = false;
            $contents = $file->getContents();
            $lines = explode("\n", $contents);

            foreach ($lines as &$line) {
                preg_match(static::RU_PATTERN, $line, $mathes);
                if ($mathes && $this->checkAdvanceConditions($line)) {
                    $matchedOrigText = $mathes[0];
                    $messageId = $this->getTranslationId($file, $matchedOrigText);
                    $convertPattern = $this->getConvertPattern($messageId);
                    $resultLine = str_replace($matchedOrigText, $convertPattern, $line);
                    $this
                        ->addMessage('Текущий тип', static::TYPE)
                        ->addMessage('Текущий файл', $file->getPathname())
                        ->addMessage('Исходная строка', $line)
                        ->addMessage('Найден текст ', $matchedOrigText)
                        ->addMessage('Результирующая строка', $resultLine)
                        ->addMessage('')
                    ;
                    if (!$emulate && $this->helper && $this->helper->ask($this->input, $this->output, $this->question)) {
                        $changed = true;
                        $line = $resultLine;
                        $this->addMessage('Внесены изменения');
                        $this->addMessage($messageId, $matchedOrigText);
                        $this->catalogue->add([$messageId => $matchedOrigText], $this->domainChecker());
                    }
                }

                if ($changed) {
                    $newfile = implode("\n", $lines);
                    $this->saveChangesToFile($file->getPathname(), $newfile);
                }

            }


        }
        if (!$emulate) {
            try {
                $this->saveTranslationMessages();
            } catch (RuTranslateException $e) {
                $this->addMessage($e->getMessage());
            }

        }

    }





    protected function getTranslationId(SplFileInfo $file, string $matchedOrigText): string
    {
        $replaceSymbols = [
            '/\s?(\,|\/|\-|\s)+\s?/i'
        ];

        $transliterator = \Transliterator::create('Russian-Latin/BGN');
//        $label = str_replace('\ʹ', '_', $matchedOrigText);
//        $label = str_replace(',', '_', $label);
//        $label = str_replace('-', '_', $label);
        $label = $transliterator->transliterate($matchedOrigText);
        $bundleName = $this->bundle->getName();
        $dir = str_replace(static::SUFFIX, '', $file->getRelativePathname());
//        $dir = str_replace('/', '.', $dir);
        $transIdPattern = sprintf($this->transIdPattert(), $bundleName, $dir, $label);
        $transIdPattern = preg_replace($replaceSymbols, '.', $transIdPattern);
        return strtolower($transIdPattern);
    }

    protected function addMessage(string $message, string $body = '')
    {

        $this->output->writeln('<info>' . $message . '</info>: '.trim($body));
        /*Сообщение в лог*/
        return $this;
    }

    protected function saveTranslationMessages(string $domain = 'messages'): bool
    {

        $messagesPath = $this->bundle->getPath() . '/Resources/translations';

        $loader = $this->container->get('translation.loader');
        $writer = $this->container->get('translation.writer');

        $loader->loadMessages($messagesPath, $this->catalogue);

        $writer->writeTranslations($this->catalogue, 'yml', ['path' => $messagesPath]);

        return true;
    }

    protected function domainChecker()
    {
        return 'messages';
    }

    protected function saveChangesToFile($filename, $lines)
    {
        $fs = new Filesystem();
        try {
            $fs->dumpFile($filename, $lines);
            $this->addMessage('Произведена запись в файл '. $filename);
        }
            catch (\InvalidArgumentException $e) {
            throw new RuTranslateException('Проблема с записью файлов ' . $e->getMessage());

        } catch (IOException $e) {

            throw new RuTranslateException('Проблема с записью файлов ' . $e->getMessage());
        }
    }

    protected function getFiles()
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $pathPatterns = $this->getPathPatterns();
        if ($fs->exists($pathPatterns['directory'])) {
            return $finder->files()->name($pathPatterns['filesPattern'])->in($pathPatterns['directory']);
        }
        return false;
    }


    protected function getPathPatterns(): array
    {
        return [
            'filesPattern' => '*' . static::SUFFIX,
            'directory' => $this->bundle->getPath() . static::FOLDER
        ];
    }

    protected function checkAdvanceConditions($line): bool
    {
        return true;
    }



    abstract protected function getConvertPattern(string $string);



    abstract protected function transIdPattert(): string;



}