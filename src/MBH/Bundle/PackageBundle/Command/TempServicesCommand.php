<?php

namespace MBH\Bundle\PackageBundle\Command;

use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TempServicesCommand
 * TODO: Remove !!!!!
 * @deprecated
 */
class TempServicesCommand extends ContainerAwareCommand
{

    const TITLES = [
        'Завтрак', 'Все включено', 'Полный пансион'
    ];

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    protected function configure()
    {
        $this
            ->setName('mbh:kr:services')
            ->setDescription('Krugoswetka services migrate')
        ;
    }

    private function toArray($collection)
    {
        return array_map(function ($val) {
            // dump($val);
            return (string) $val['_id'];
        }, $collection);
    }
    
    private function services()
    {
        return $this->dm->getRepository('MBHPriceBundle:Service')
            ->createQueryBuilder()
            ->field('fullTitle')->in(self::TITLES);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = new \DateTime();
        $this->dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $time = $start->diff(new \DateTime());
        $packages = $this->dm->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->select(['_id'])
            ->field('end')->gte(new \DateTime('midnight'))
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        $ids = $this->toArray($packages);

        //change services type
        $services = $this->services()
            ->getQuery()
            ->execute();

        foreach ($services as $service) {
            $service->setCalcType('per_stay');
            if ($service->getName() == 'Завтрак') {
                $service->setIncludeArrival(false);
            } {
                $service->setIncludeArrival(true);
            }
            $service->setIncludeDeparture(true);
            $service->setRecalcWithPackage(true);
            $this->dm->persist($service);
            $this->dm->flush();
        }

        $services = $this->services()
            ->select(['_id', 'fullTitle'])
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        $servicesIds = $this->toArray($services);

        $packageServices = $this->dm->getRepository('MBHPackageBundle:PackageService')
            ->createQueryBuilder()
            ->select(['_id'])
            ->field('package.id')->in($ids)
            ->field('service.id')->in($servicesIds)
            ->field('note')->equals('Услуга по умолчанию')
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();
        $packageServicesIds = $this->toArray($packageServices);
        $this->dm->clear();

        $count = 0;
        foreach ($packageServicesIds as $packageServiceId) {
            $packageService = $this->dm->getRepository('MBHPackageBundle:PackageService')
                ->find($packageServiceId);
            if ($packageService->getService()->getFullTitle() == 'Завтрак') {
                $new = new PackageService();
                $new->setService($packageService->getService())
                    ->setPackage($packageService->getPackage())
                    ->setPrice($packageService->getPrice())
                    ->setRecalcWithPackage(true)
                    ->setIncludeArrival(false)
                    ->setIncludeDeparture(true)
                    ->setTotal($packageService->getTotal())
                    ->setTotalOverwrite($packageService->getTotalOverwrite())
                    ->setAmount($packageService->getAmount())
                    ->setPersons($packageService->getPersons())
                    ->setNights($packageService->getNights())
                    ->setTime($packageService->getTime())
                    ->setNote('Автосоздана при обновлении #' . $packageService->getId())
                    ->setIsCustomPrice($packageService->getIsCustomPrice())
                    ->setBegin(null)
                    ->setEnd(null)
                ;
                $this->dm->persist($new);
                $count++;
                $output->writeln($count . '. complete #' . $packageService->getId());
            }
            $this->dm->remove($packageService);
            $this->dm->flush();
            $this->dm->clear();
            // dump($service);
        }

        $time = $start->diff(new \DateTime());
        $output->writeln('Migration complete. Elapsed time: ' . $time->format('%H:%I:%S') . '. Packages: ' . $count);
    }
}
