<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 8/15/16
 * Time: 12:35 PM
 */

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;


use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class AllTraslateShower implements RuTranslateInterface
{

    private $input;

    private $output;

    private $container;

    private $bundle;

    /**
     * AllTraslateShower constructor.
     * @param $input
     * @param $output
     * @param $container
     */
    public function __construct(InputInterface $input, OutputInterface $output, ContainerInterface $container, BundleInterface $bundle = null)
    {
        $this->input = $input;
        $this->output = $output;
        $this->container = $container;
        $this->bundle = $bundle;
    }


    /**
     * @return mixed
     */
    public function findEntry()
    {
        $bundleConverters = $this->getConverters();
        /** @var RuTranslateInterface $converter */
        foreach ($bundleConverters as $name => $converter) {
            /** @var AbstractTranslateConverter $convert */
            foreach ($converter as $convert) {
                $convert->findEntry();
            }
        }
    }

    /**
     * @return mixed
     */
    public function convert(QuestionHelper $helper)
    {
        $bundleConverters = $this->getConverters();
        /** @var RuTranslateInterface $converter */
        foreach ($bundleConverters as $name => $converter) {
            /** @var AbstractTranslateConverter $convert */
            foreach ($converter as $convert) {
                $convert->convert($helper);
            }
        }
    }

    private function getConverters(): array
    {
        $bundles = $this->getBudnles();
        $converters = null;
        $bundleConverter = null;
        /** @var BundleInterface $bundle */
        foreach ($bundles as $bundle) {
                switch ($this->input->getOption('type')) {
                    case 'twig':
                        $converters = [new TwigTranslateConverter($bundle, $this->input, $this->output, $this->container)];
                        break;
                    case 'form':
                        $converters = [new FormTranslateConverter($bundle, $this->input, $this->output, $this->container)];
                        break;
                    case 'doc':
                        $converters = [new DocumentTranslateConverter($bundle, $this->input, $this->output, $this->container)];
                        break;
                    case 'all':
                        $converters = [
                            new TwigTranslateConverter($bundle, $this->input, $this->output, $this->container),
                            new FormTranslateConverter($bundle, $this->input, $this->output, $this->container),
                            new DocumentTranslateConverter($bundle, $this->input, $this->output, $this->container)
                        ];
                        break;
                    default:
                        throw new InvalidArgumentException('Wrong type (twig/form/doc) only');
                        break;
                }

            $bundleConverter[$bundle->getName()] = $converters;
        }
        if (!$bundleConverter) {
            throw new RuTranslateException('Не вернулись конвертеры по заданным условиям');
        }
        return $bundleConverter;
    }

    private function getBudnles(): array
    {
        if ($this->bundle) {
            return [$this->bundle];
        }
        return $this->container->get('kernel')->getBundles();
    }

}