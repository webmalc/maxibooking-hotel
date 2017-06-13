<?php

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;


use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;


/**
 * Class AbstractTranslateConverter
 * @package MBH\Bundle\BaseBundle\Lib\RuTranslateConverter
 */
abstract class AbstractTranslateConverter implements TranslateInterface
{
    /**
     *
     */
    const TRANSLATE_FOLDER = '/Resources/translations';

    /**
     *
     */
    const TYPE = "AbstractParser";

    const FILE_SUFFIX = '';

    const FOLDER = '';
    public static $counter = 0;
    /**
     * @var BundleInterface
     */
    protected $bundle;
    /**
     * @var OutputInterface
     */
    protected $output;
    /**
     * @var InputInterface
     */
    protected $input;
    /**
     * @var
     */
    protected $helper;
    /**
     * @var
     */
    protected $question;
    /**
     * @var MessageCatalogue
     */
    protected $catalogue;
    /**
     * @var string
     */
    protected $pattern = '/([А-Яа-яЁё]+\s*[\-\.\?\,]?\s*)+/u';
    private $loader;
    private $writer;

    protected $logger;

    protected $key;

    public function __construct(
        TranslationLoader $loader,
        TranslationWriter $writer,
        LoggerInterface $logger,
        string $pattern = null,
        $locale = 'ru'


    ) {
        $this->loader = $loader;
        $this->writer = $writer;
        $this->catalogue = new MessageCatalogue($locale);
        $this->logger = $logger;
        if ($pattern) {
            $this->pattern = $pattern;
        }
    }


    public function interactiveConvert(
        BundleInterface $bundle = null,
        InputInterface $input,
        OutputInterface $output,
        Helper $helper,
        bool $dryRun = true
    ) {
        $this->bundle = $bundle;
        $this->helper = $helper;
        $this->input = $input;
        $this->output = $output;
        $this->question = new ConfirmationQuestion('Применить изменения? ', false);
        $this->execute($dryRun);
    }

    /**
     * @param bool $dryRun
     * @return bool
     */
    protected function execute($dryRun = true)
    {
        $files = $this->getFiles();
        if (!$files) {
            $this->addMessage(
                'Пропускаем, нет директории '.static::FOLDER.' в текущем бандле ',
                $this->bundle->getName()
            );

            return true;
        }
        foreach ($files as $file) {
            $changed = false;
            $contents = $file->getContents();
            $lines = explode("\n", $contents);
            foreach ($lines as &$line) {
                preg_match($this->pattern, $line, $matches);

                if ($matches && $this->checkAdvanceConditions($line)) {
                    self::$counter++;

                    $matchedOrigText = $matches[0];
                    $messageId = $this->createTranslationId($file, $matchedOrigText);

                    $convertPattern = $this->getConvertPattern($messageId);

                    $resultLine = str_replace($matchedOrigText, $convertPattern, $line);

                    $this
                        ->addMessage('Текущий тип', static::TYPE)
                        ->addMessage('Текущий файл', $file->getPathname())
                        ->addMessage('Исходная строка', $line)
                        ->addMessage('Найден текст ', $matchedOrigText)
                        ->addMessage('Результирующая строка', $resultLine)
                        ->addMessage('______________________________________________________________________');
                    if (!$dryRun && $this->helper && $this->helper->ask(
                            $this->input,
                            $this->output,
                            $this->question
                        )
                    ) {
                        $line = $resultLine;
                        $this->addMessage('Внесены изменения');
                        $this->addMessage($messageId, $matchedOrigText);
                        $this->catalogue->add([$messageId => $matchedOrigText], $this->getTranslationDomain());
                        $changed = true;
                    }
                }

                if ($changed) {
                    $newFile = implode("\n", $lines);
                    $this->saveChangesToFile($file->getPathname(), $newFile);
                }

            }


        }
        if (!$dryRun) {
            try {
                $messagesPath = $this->bundle->getPath().'/Resources/translations';
                $this->loader->loadMessages($messagesPath, $this->catalogue);
                foreach ($this->catalogue->all() as $key => $messages) {
                    $this->key = $key;

                    $newMessages = array_map(function ($item) {
                        if (preg_match('/\'|\`|\?|\.$/', $item)) {
                            $this->logger->warning($this->bundle->getName().' | '.$this->key.' | '.$item);
                        }

                        return preg_replace('/\'|\`|\?|\.$/', '', $item);
                    }, array_flip($messages));

                    $this->catalogue->replace(array_flip($newMessages), $key);
                }
                $this->writer->writeTranslations($this->catalogue, 'yml', ['path' => $messagesPath]);
            } catch (RuTranslateException $e) {
                $this->addMessage($e->getMessage());
            }

        }

    }

    protected function getFiles(): ?Finder
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $dir = $this->bundle->getPath().static::FOLDER;
        if ($fs->exists($dir)) {
            return $finder->files()->name('*'.static::FILE_SUFFIX)->in($dir);
        } else {
            return null;
        }

    }

    /**
     * @param string $message
     * @param string $body
     * @return $this
     */
    protected function addMessage(string $message, string $body = '')
    {

        $this->output->writeln('<info>'.$message.'</info>: '.trim($body));

        /*Сообщение в лог*/

        return $this;
    }

    /**
     * @param string $line
     * @return bool
     */
    abstract protected function checkAdvanceConditions(string $line): bool;

    /**
     * @param SplFileInfo $file
     * @param string $matchedOrigText
     * @return string
     */
    protected function createTranslationId(SplFileInfo $file, string $matchedOrigText): string
    {
        $replaceSymbols = [
            '/\s?(\,|\/|\-|\s)+\s?/i',
        ];

        $transliterator = \Transliterator::create('Russian-Latin/BGN');
        $label = $transliterator->transliterate($matchedOrigText);
        $bundleName = $this->bundle->getName();
        $dir = str_replace(static::FILE_SUFFIX, '', $file->getRelativePathname());
        $transIdPattern = sprintf($this->transIdPattert(), $bundleName, $dir, $label);
        $transIdPattern = preg_replace($replaceSymbols, '.', $transIdPattern);

        return strtolower($transIdPattern);
    }

    /**
     * @return string
     */
    abstract protected function transIdPattert(): string;

    /**
     * @param string $transliteratedString
     * @return mixed
     */
    abstract protected function getConvertPattern(string $transliteratedString);

    /**
     * @return string
     */
    protected function getTranslationDomain()
    {
        return 'messages';
    }

    /**
     * @param $filename
     * @param $lines
     * @throws RuTranslateException
     */
    protected function saveChangesToFile($filename, $lines)
    {
        $fs = new Filesystem();
        try {
            $fs->dumpFile($filename, $lines);
            $this->addMessage('Произведена запись в файл '.$filename);
        } catch (\InvalidArgumentException | IOException $e) {
            throw new RuTranslateException('Проблема с записью файлов '.$e->getMessage());
        }
    }

    public function canHandle(string $type): bool
    {
        return static::HANDLE_TYPE == $type;
    }

}