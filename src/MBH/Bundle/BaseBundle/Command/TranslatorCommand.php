<?php

namespace MBH\Bundle\BaseBundle\Command;


use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use MBH\Bundle\BaseBundle\Lib\RuTranslateConverter\AbstractTranslateConverter;
use MBH\Bundle\BaseBundle\Lib\RuTranslateConverter\TranslateInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class TranslatorCommand extends ContainerAwareCommand
{

    private $converters;

    public function __construct($name = null)
    {
        $this->converters = new \SplObjectStorage();
        parent::__construct($name);
    }

    public function addConverter(AbstractTranslateConverter $converter)
    {
        $this->converters->attach($converter);
    }

    protected function configure()
    {
        $this
            ->setName('mbh:translation')
            ->setDefinition(
                [
                    new InputArgument('action', InputArgument::OPTIONAL, 'show or convert', 'show'),
                    new InputOption('type', null, InputOption::VALUE_OPTIONAL, 'service/twig/form/doc/all', 'all'),
                    new InputOption('bundle', null, InputOption::VALUE_OPTIONAL, 'The bundle\'s name'),
                    new InputOption('force', null, InputOption::VALUE_NONE, 'Not emulate if --force'),
                ]
            )
            ->
            setDescription('Show/convert no translated twig or forms')
            ->setHelp(
                <<<EOF
                The <info>%command.name%</info> command guess russian not translate symbols in twig files
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        /** @var Kernel $kernel */
        $kernel = $this->getContainer()->get('kernel');
        $bundles = null;
        if (null !== $input->getOption('bundle')) {
            try {
                /** @var Bundle $foundBundle */
                $bundles = [$kernel->getBundle($input->getOption('bundle'))];
            } catch (\InvalidArgumentException $e) {
                $output->writeln($e->getMessage());
                throw new InvalidArgumentException('Невозможно найти бандл с именем '.$input->getArgument('bundle'));
            }
        } else {
            $bundles = $this->getContainer()->get('mbh.helper')->getMBHBundles();
        }

        $action = $input->getArgument('action');
        $type = $input->getOption('type');
        $dryRun = 'show' === $action;
        /** @var TranslateInterface $converter */
        foreach ($this->converters as $converter) {
            if ('all' == $type || $converter->canHandle($type)) /** @var AbstractTranslateConverter $converter */ {
                foreach ($bundles as $bundle) {
                    /** @var BundleInterface $bundle */
                    $converter->interactiveConvert($bundle, $input, $output, $this->getHelper('question'), $dryRun);
                }
            }
        }

        $output->writeln('Было обнаружено всего '.AbstractTranslateConverter::$counter.' записей');
    }

}