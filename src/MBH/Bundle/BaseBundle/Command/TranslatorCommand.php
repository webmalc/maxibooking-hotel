<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/10/16
 * Time: 1:53 PM
 */

namespace MBH\Bundle\BaseBundle\Command;


use Symfony\Bridge\Twig\TwigEngine;
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
use Symfony\Component\Translation\Writer\TranslationWriter;

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
            $changed = false;
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
                        $changed = true;
                        $line = $resultLine;
                        $res[] = [
                            'orig' => $originalText,
                            'pathInDomain' => $fullTranslatePath,
//                            'pathinfile' => 'Тут сформировать путь до перевода',
//                            'translate' => 'А тут сам перевод'
                        ];
                        $messages[$fullTranslatePath] =$originalText;
                    };

                }

            }
            if ($changed) {
                $output->writeln('Dump File '.$file->getPathname());
                $newfile = implode("\n", $arrlines);
//                $fs->dumpFile($file->getPathname(), $newfile );
            }

        }

        $translationsPath = $rootPath.'/Resources/translations';
        /** @var TranslationLoader $loader */
        $catalogue = new MessageCatalogue('ru');
        $loader = $this->getContainer()->get('translation.loader');
        $loader->loadMessages($translationsPath, $catalogue);
        $catalogue->add($messages);
        /** @var TranslationWriter $writer */
        $writer->writeTranslations($catalogue, 'yml', ['path' => $translationsPath, 'default_locale' => $this->getContainer()->getParameter('kernel.default_locale') ] );

        return 0;

    }

}