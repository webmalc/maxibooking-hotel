<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/10/16
 * Time: 1:53 PM
 */

namespace MBH\Bundle\BaseBundle\Command;


use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use MBH\Bundle\BaseBundle\Lib\RuTranslateConverter\TranslateConverterContainer;
use MBH\Bundle\BaseBundle\Lib\RuTranslateConverter\DocumentTranslateConverter;
use MBH\Bundle\BaseBundle\Lib\RuTranslateConverter\FormTranslateConverter;
use MBH\Bundle\BaseBundle\Lib\RuTranslateConverter\TwigTranslateConverter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Translation\MessageCatalogue;

class TranslatorCommand extends ContainerAwareCommand
{

    const ACTIONS = ['show', 'convert'];

    protected function configure()
    {
        $this
            ->setName('mbh:translation')
            ->setDefinition(array(
                new InputArgument('action', InputArgument::OPTIONAL, 'show or convert', 'show'),
                new InputOption('type', null, InputOption::VALUE_OPTIONAL, 'twig/form/doc/all', 'all'),
                new InputOption('bundle', null, InputOption::VALUE_OPTIONAL, 'The bundle name '),

            ))
            ->setDescription('Show/convert no translated twig or forms')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command guess russian not translate symbols in twig files
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Kernel $kernel */
        $kernel = $this->getContainer()->get('kernel');
        $bundle = null;
        if (null !== $input->getOption('bundle')) {
            try {
                /** @var Bundle $foundBundle */
                $bundle = $kernel->getBundle($input->getOption('bundle'));
            } catch (\InvalidArgumentException $e) {
                $output->writeln($e->getMessage());
                throw new InvalidArgumentException('Невозможно найти бандл с именем '. $input->getArgument('bundle'));
            }
        }

        $converter = new TranslateConverterContainer($input, $output, $this->getContainer(), $bundle);

        $helper = $this->getHelper('question');
        $action = $input->getArgument('action');

        if (in_array($action, self::ACTIONS)) {

        }
        if ($action == 'show') {
            $converter->findEntry();
        } elseif ($action == 'convert') {
            $converter->convert($helper);
        } else {
            throw new InvalidArgumentException('Wrong action, (show/convert) only ');
        }
    }

}