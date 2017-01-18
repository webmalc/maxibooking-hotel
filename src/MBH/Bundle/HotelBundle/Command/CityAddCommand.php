<?php

namespace MBH\Bundle\HotelBundle\Command;

use MBH\Bundle\HotelBundle\Document\City;
use MBH\Bundle\HotelBundle\Document\Country;
use MBH\Bundle\HotelBundle\Document\Region;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CityAddCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:city:add')
            ->setDescription('Add a city in the database.')
            ->addArgument('country', InputArgument::REQUIRED, 'Country title')
            ->addArgument('region', InputArgument::REQUIRED, 'Region title')
            ->addArgument('city', InputArgument::REQUIRED, 'City title');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        if ($dm->getRepository('MBHHotelBundle:City')->findOneBy(['title' => $input->getArgument('city')])) {
            $output->writeln('<error>Error. City already exist in the database!!!</error>');

            return false;
        }

        $country = $dm->getRepository('MBHHotelBundle:Country')->findOneBy(['title' => $input->getArgument('country')]);
        if (!$country) {
            $country = new Country();
            $country->setTitle($input->getArgument('country'));

            $dm->persist($country);
        }

        $region = $dm->createQueryBuilder('MBHHotelBundle:Region')
            ->field('title')->equals($input->getArgument('region'))
            ->field('country.id')->equals($country->getId())
            ->limit(1)
            ->getQuery()
            ->getSingleResult();

        if (!$region) {
            $region = new Region();
            $region->setCountry($country)->setTitle($input->getArgument('region'));

            $dm->persist($region);
        }

        $city = new City();
        $city->setCountry($country)->setRegion($region)->setTitle($input->getArgument('city'));

        $dm->persist($city);
        $dm->flush();

        $output->writeln('City added (' . $country . ', ' . $region . ', ' . $city . '). Id: ' . $city->getId());
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('country')) {
            $arg = $this->getHelper('dialog')->askAndValidate(
                $output,
                '<question>Please enter a country title:</question>',
                function ($arg) {
                    if (empty($arg)) {
                        throw new \Exception('Country title can not be empty');
                    }

                    return $arg;
                }
            );
            $input->setArgument('country', $arg);
            unset($arg);
        }
        if (!$input->getArgument('region')) {
            $arg = $this->getHelper('dialog')->askAndValidate(
                $output,
                '<question>Please enter a region title:</question>',
                function ($arg) {
                    if (empty($arg)) {
                        throw new \Exception('Region title can not be empty');
                    }

                    return $arg;
                }
            );
            $input->setArgument('region', $arg);
            unset($arg);
        }
        if (!$input->getArgument('city')) {
            $arg = $this->getHelper('dialog')->askAndValidate(
                $output,
                '<question>Please enter a city title:</question>',
                function ($arg) {
                    if (empty($arg)) {
                        throw new \Exception('City title can not be empty');
                    }

                    return $arg;
                }
            );
            $input->setArgument('city', $arg);
            unset($arg);
        }
    }
}