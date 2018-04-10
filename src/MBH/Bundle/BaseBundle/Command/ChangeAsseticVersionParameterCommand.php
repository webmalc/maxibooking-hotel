<?php

namespace MBH\Bundle\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeAsseticVersionParameterCommand extends ContainerAwareCommand
{
    const ASSETIC_VERSION_PARAMETER_NAME = 'assetic_version';
    const COMMAND_NAME = 'mbhbase:change_assetic_version';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Change assetic version in version.yml file');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $versionFilePath = $container->get('kernel')->getRootDir() . '/config/version.yml';
        $newAsseticVersion = $container->get('mbh.helper')->getRandomString();

        $container
            ->get('mbh.yaml_manager')
            ->setSingleEnclosedParameter($versionFilePath, 'parameters', self::ASSETIC_VERSION_PARAMETER_NAME, $newAsseticVersion);
    }
}
