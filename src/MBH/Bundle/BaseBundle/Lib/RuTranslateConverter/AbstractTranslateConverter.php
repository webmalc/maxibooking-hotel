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
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Class AbstractTranslateConverter
 * @package MBH\Bundle\BaseBundle\Lib\RuTranslateConverter
 */
abstract class AbstractTranslateConverter
{

    /**
     *
     */
    const TRANSLATE_FOLDER = '/Resources/translations';

    /**
     *
     */
    const RU_PATTERN = '/([А-Яа-яЁё]+\s*)+/u';

    /**
     * @var BundleInterface
     */
    protected $bundle;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Input|InputInterface
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
     * AbstractTranslateConverter constructor.
     * @param BundleInterface $bundle
     * @param Input|InputInterface $input
     * @param OutputInterface $output
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
     * @param null|Helper|QuestionHelper $helper
     */
    public function convert(QuestionHelper $helper)
    {
        $this->helper = $helper;
        $this->question = new ConfirmationQuestion('Применить изменения? ', false);
        $this->execute(false);
    }

    /**
     *
     */
    public function findEntry()
    {
        $this->execute();
    }


    /**
     * @param string $message
     * @return $this
     */
    protected function addMessage(string $message)
    {
        $this->output->writeln('<info>' . $message . '</info>');
        /*Сообщение в лог*/
        return $this;
    }

    /**
     * @param string $domain
     * @return bool
     */
    protected function saveTranslationMessages(string $domain = 'messages'): bool
    {

        $messagesPath = $this->bundle->getPath() . '/Resources/translations';

        $loader = $this->container->get('translation.loader');
        $writer = $this->container->get('translation.writer');

        $loader->loadMessages($messagesPath, $this->catalogue);

        $writer->writeTranslations($this->catalogue, 'yml', ['path' => $messagesPath]);

        return true;
    }

    /**
     * @return string
     */
    protected function domainChecker()
    {
        return 'messages';
    }

    /**
     * @param $lines
     * @param $filename
     * @throws RuTranslateException
     */
    protected function saveChangesToFile($filename, $lines)
    {
        $fs = new Filesystem();
        try {
            $fs->dumpFile($filename, $lines);
        } catch (IOException $e) {
            throw new RuTranslateException('Проблема с записью файлов ' . $e->getMessage());
        }
    }


    /**
     * @return array
     */
    abstract protected function getPathPatterns(): array;

    /**
     * @param string $string
     * @return mixed
     */
    abstract protected function getConvertPattern(string $string);

    /**
     * @param SplFileInfo $file
     * @param string $matchedOrigText
     * @return string
     */
    abstract protected function getTransIdPattern(SplFileInfo $file, string $matchedOrigText): string;

    /**
     * @param bool $emulate
     * @return mixed
     */
    abstract protected function execute($emulate = true);


}