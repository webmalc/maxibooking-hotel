<?php
/**
 * Created by PhpStorm.
 * Date: 17.10.18
 */

namespace MBH\Bundle\PriceBundle\Command;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffCombinationHolder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MoveTariffIntoTariffCombinationMigrateCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    protected function configure()
    {
        $this
            ->setName('mbh:price:move_tariff_migrate')
            ->setDescription('Move tariff from property "merging tariff" into "tariff combination holders". (Without remove)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dm = $this->getContainer()->get('doctrine.odm.mongodb.document_manager');

        $tariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')
            ->findBy(['mergingTariff' => ['$ne' => null]]);


        $count = 0;
        /** @var Tariff $tariff */
        foreach ($tariffs as $tariff) {
            $holder = new TariffCombinationHolder();
            $holder->setParentId($tariff->getId());
            $holder->setCombinationTariffId($tariff->getMergingTariff()->getId());
            $holder->setPosition(1);

            $tariff->setTariffCombinationHolders(new ArrayCollection([$holder]));

            $this->dm->persist($tariff);
            $count++;
        }

        $this->dm->flush();

        $output->writeln(sprintf('Transferred from %s tariffs.', $count));
    }
}