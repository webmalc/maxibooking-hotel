<?php

namespace MBH\Bundle\BaseBundle\Lib\RuTranslateConverter;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Interface RuTranslateInterface
 * @package MBH\Bundle\BaseBundle\Lib\RuTranslateConverter
 */
interface TranslateInterface
{

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Helper $helper
     * @param bool $dryRun
     * @return mixed
     */
    public function interactiveConvert(
        BundleInterface $bundle,
        InputInterface $input,
        OutputInterface $output,
        Helper $helper,
        bool $dryRun = true
    );

    /**
     * @param string $type
     * @return bool
     */
    public function canHandle(string $type): bool;
}