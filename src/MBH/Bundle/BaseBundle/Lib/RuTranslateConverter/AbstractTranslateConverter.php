<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/11/16
 * Time: 4:57 PM
 */

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;


use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\Input;
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

abstract class AbstractTranslateConverter
{

    const TRANSLATE_FOLDER = '/Resources/translations';

    const RU_PATTERN = '/([А-Яа-яЁё]+\s*)+/u';

    protected $bundle;

    private $output;

    private $container;

    private $input;

    private $helper;

    private $question;

    protected $catalogue;

    /**
     * AbstractTranslateConverter constructor.
     * @param BundleInterface $bundle
     * @param OutputInterface $output
     * @param Input $input
     * @param ContainerInterface $container
     */
    public function __construct(BundleInterface $bundle, InputInterface $input, OutputInterface $output, ContainerInterface $container)
    {
        $this->bundle = $bundle;
        $this->output = $output;
        $this->container = $container;
        $this->input = $input;
        $this->catalogue = new MessageCatalogue('ru');
    }

    /**
     * @param Helper|null $helper
     */
    public function convert(QuestionHelper $helper)
    {
        $this->helper = $helper;
        $this->question = new ConfirmationQuestion('Применить изменения?');
        $this->execute(false);
    }

    public function findEntry()
    {
        $this->execute();
    }

    private function execute($emulate = true)
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

    protected function addMessage(string $message)
    {
        $this->output->writeln('<info>'.$message.'</info>');
        /*Сообщение в лог*/
        return $this;
    }

    protected function saveTranslationMessages(string $domain = 'messages'): bool
    {
        if (!$messages = $this->messages) {
            return false;
        };

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

    protected function saveChangesToFile($lines, $filename)
    {
        $fs = new Filesystem();
        try {
            true;
            $fs->dumpFile($filename, $lines);
        } catch (IOException $e) {
            throw new RuTranslateException('Проблема с записью файлов ' . $e->getMessage());
        }
    }



    abstract protected function getPathPatterns(): array;

    abstract protected function getConvertPattern(string $string);

    abstract protected function getTransIdPattern(SplFileInfo $file, string $matchedOrigText): string ;


}