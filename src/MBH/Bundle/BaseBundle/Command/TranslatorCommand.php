<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/10/16
 * Time: 1:53 PM
 */

namespace MBH\Bundle\BaseBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Translation\Catalogue\DiffOperation;
use Symfony\Component\Translation\Catalogue\MergeOperation;
use Symfony\Component\Translation\Catalogue\OperationInterface;
use Symfony\Component\Translation\Catalogue\TargetOperation;
use Symfony\Component\Translation\Extractor\ChainExtractor;
use Symfony\Component\Translation\MessageCatalogue;

class TranslatorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:translation:guessermbh')
            ->setDefinition(array(
//                new InputArgument('show', InputArgument::OPTIONAL, 'Show russian symbols'),
                new InputArgument('bundle', InputArgument::OPTIONAL, 'The bundle name '),

            ))
            ->setDescription('Show no translated twig files')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command guess russian not translate symbols in twig files
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $finder = new Finder();

        $writer = $this->getContainer()->get('translation.writer');

        /** @var Kernel $kernel */
        $kernel = $this->getContainer()->get('kernel');
        if (null === $input->getArgument('bundle')) {
            return 1;
        }
        try {
            /** @var Bundle $foundBundle */
            $foundBundle = $kernel->getBundle($input->getArgument('bundle'));
            $domainName = str_replace('MBH', '', $foundBundle->getName()).'_test';
            $rootPath = $foundBundle->getPath();
            $currentName = $foundBundle->getName();
        } catch (\InvalidArgumentException $e) {
            // such a bundle does not exist, so treat the argument as path
            $rootPath = $input->getArgument('bundle');
            $currentName = $rootPath;
            if (!is_dir($rootPath)) {
                throw new \InvalidArgumentException(sprintf('<error>"%s" is neither an enabled bundle nor a directory.</error>', $rootPath));
            }
        }


        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Применить изменения?');

        /** @var \Transliterator $transliterator */
        $transliterator = \Transliterator::create('Russian-Latin/BGN');
        $files = $finder->files()->name('*.twig')->in($rootPath);
        $pattern = '/([А-Яа-яЁё]+\s*)+/u';
        $res = [];

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $contents = $file->getContents();
            $arrlines = explode("\n", $contents);
            foreach ($arrlines as &$line) {
                preg_match($pattern, $line, $matches );
                if ($matches) {
                    $output->writeln('Файл'. $file->getPathname());
                    $output->writeln('Исходная строка '. $line);
                    $originalText = $matches[0];
                    $output->writeln('Найден текст '. $originalText);
                    $translitText = $transliterator->transliterate(str_replace(' ', '', $originalText));
                    $fullTranslatePath = strtolower(str_replace('/', '.', $file->getRelativePath())) . '.' . strtolower($translitText);
                    $toPattern = '{{ \''.$fullTranslatePath.'\'| trans }}';
                    $resultLine = str_replace($originalText, $toPattern, $line);
                    $output->writeln('Результирующая строка '. $resultLine);
                    if ($helper->ask($input, $output, $question)) {
                        $line = $resultLine;
                        $res[] = [
                            'orig' => $originalText,
                            'pathInDomain' => $fullTranslatePath,
//                            'pathinfile' => 'Тут сформировать путь до перевода',
//                            'translate' => 'А тут сам перевод'
                        ];
                    };

                }

            }

            $newfile = implode('\n', $arrlines);
            continue;

        }




        return 0;
        exit;
        $translationsPath = $rootPath.'/Resources/translations';

        $output->writeln(sprintf('Generating "<info>%s</info>" translation files for "<info>%s</info>"', 'ru', $currentName));
        // load any messages from templates
        $extractedCatalogue = new MessageCatalogue('ru');
        $output->writeln('Parsing templates');

        /** @var ChainExtractor $extractor */
        $extractor = $this->getContainer()->get('translation.extractor');
        $extractor->setPrefix('');
        $extractor->extract($rootPath.'/Resources/views/', $extractedCatalogue);

        $currentCatalog = new MessageCatalogue('ru');


        /** @var TranslationLoader $loader */
        $loader = $this->getContainer()->get('translation.loader');
        $loader->loadMessages($translationsPath, $currentCatalog);

//        $operation = $input->getOption('clean')
//            ? new DiffOperation($currentCatalogue, $extractedCatalogue)
//            : new MergeOperation($currentCatalogue, $extractedCatalogue);

        return 0;

    }

}