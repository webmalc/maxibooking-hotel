<?php


namespace MBH\Bundle\BaseBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AclMigrateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:acl:migration')
            ->setDescription('Migrate from acl to voter.');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $this->getContainer()->get('mbh.service.acl_migratory');

        $output->writeln('Start hotel Migrate');
        $migrated = $service->doHotelMigrate();
        $output->writeln(sprintf('Hotel migrate done. Migrated %s acls.', $migrated));

        $output->writeln('Start package Migrate');
        $migrated = $service->doPackageMigrate();
        $output->writeln(sprintf('Package migrate done. Migrated %s acls.', $migrated));

    }


}