<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/10/16
 * Time: 1:53 PM
 */

namespace MBH\Bundle\BaseBundle\Command;


use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use MBH\Bundle\BaseBundle\Lib\RuTranslateConverter\FormTranslateConverter;
use MBH\Bundle\BaseBundle\Lib\RuTranslateConverter\RuTranslateException;
use MBH\Bundle\BaseBundle\Lib\RuTranslateConverter\TwigTranslateConverter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;

class TranslatorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:translation:guessermbh')
            ->setDefinition(array(
                new InputArgument('action', InputArgument::REQUIRED, 'show or convert'),
                new InputArgument('bundle', InputArgument::REQUIRED, 'The bundle name '),
                new InputOption('type', null, InputOption::VALUE_REQUIRED, 'twig or form')

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
        if (null === $input->getArgument('bundle') or null === $input->getArgument('action')) {
            return 1;
        }
        try {
            /** @var Bundle $foundBundle */
            $bundle = $kernel->getBundle($input->getArgument('bundle'));
        } catch (\InvalidArgumentException $e) {
            $output->writeln($e->getMessage());
            throw new InvalidArgumentException('Невозможно найти бандл с именем '. $input->getArgument('bundle'));
        }

        $helper = $this->getHelper('question');
        switch ($input->getOption('type')) {
            case 'twig':
                $converter = new TwigTranslateConverter($bundle, $input, $output, $this->getContainer());
                break;
            case 'form':
                $converter = new FormTranslateConverter($bundle, $input, $output, $this->getContainer());
                break;
            default:
                throw new InvalidArgumentException('Wrong type (twig/form) only');
                break;
        }

        switch ($input->getArgument('action')) {
            case 'show':
                $converter->findEntry();
                break;
            case 'convert':
                $converter->convert($helper);
                break;
            default:
                throw new InvalidArgumentException('Wrong action, (show/convert) only ');
                break;
        }

    }

}