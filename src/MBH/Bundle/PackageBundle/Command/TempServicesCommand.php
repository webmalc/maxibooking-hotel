<?php

namespace MBH\Bundle\PackageBundle\Command;

use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PriceBundle\Document\Service;
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
        'Завтрак', 'Все включено', 'Полный пансион', 'Полдник', 'Обед', 'Ужин'
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
                $service->setIncludeDeparture(true);
            } else {
                $service->setIncludeArrival(true);
                $service->setIncludeDeparture(false);
            }
            $service->setRecalcWithPackage(true);
            $service->setIsEnabled(true);
            $this->dm->persist($service);
            $this->dm->flush();
        }

        $categories = $this->dm->getRepository('MBHPriceBundle:ServiceCategory')
            ->createQueryBuilder()
            // ->field('fullTitle')->equals('price.datafixtures.mongodb.servicedata.eat')
            ->field('fullTitle')->equals('Питание')
            ->field('system')->equals(true)
            ->getQuery()
            ->execute()
        ;

        //create service
        foreach ($categories as $key => $category) {
            $s = new Service();
            $s->setTitle('Полдник')
                ->setFullTitle('Полдник')
                ->setPrice(0)
                ->setCalcType('per_stay')
                ->setRecalcWithPackage(true)
                ->setIncludeArrival(true)
                ->setIncludeDeparture(false)
                ->setCategory($category)
            ;
            $this->dm->persist($s);
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
            // ->field('note')->equals('Услуга по умолчанию')
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();
        $packageServicesIds = $this->toArray($packageServices);
        $this->dm->clear();

        $count = 0;
        $broken = [];
        foreach ($packageServicesIds as $packageServiceId) {
            $packageService = $this->dm->getRepository('MBHPackageBundle:PackageService')
                ->find($packageServiceId);

            /// Breakfast
            if ($packageService->getService()->getFullTitle() == 'Завтрак') {
                if ($packageService->getNote() !== 'Услуга по умолчанию') {
                    $broken[] = $packageService->getPackage();
                    continue;
                }
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
                $output->writeln($count . '. complete #' . $packageService->getPackage()->getId());
            } // Full-board
            elseif (in_array($packageService->getService()->getFullTitle(), ['Все включено', 'Полный пансион'])) {
                if ($packageService->getNote() !== 'Услуга по умолчанию') {
                    $broken[] = $packageService->getPackage();
                    continue;
                }
                $package = $packageService->getPackage();
                $cat = $this->dm->getRepository('MBHPriceBundle:ServiceCategory')
                    ->createQueryBuilder()
                    ->field('fullTitle')->equals('Питание')
                    ->field('system')->equals(true)
                    ->field('hotel.id')->equals($package->getRoomType()->getHotel()->getId())
                    ->getQuery()
                    ->getSingleResult()
                ;
                foreach ($this->dm->getRepository('MBHPriceBundle:Service')
                         ->createQueryBuilder()
                         ->field('category.id')->equals($cat->getId())
                         ->field('fullTitle')->in(['Завтрак', 'Полдник', 'Обед', 'Ужин'])
                         ->getQuery()->execute() as $sr) {
                    $new = new PackageService();
                    $new->setService($sr)
                        ->setPackage($packageService->getPackage())
                        ->setPrice(0)
                        ->setRecalcWithPackage($sr->isRecalcWithPackage())
                        ->setIncludeArrival($sr->isIncludeArrival())
                        ->setIncludeDeparture($sr->isIncludeDeparture())
                        ->setTotal(0)
                        ->setTotalOverwrite(null)
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
                    $output->writeln($count . '. complete #' . $packageService->getPackage()->getId());
                }
            } else {
                continue;
            }

            $this->dm->remove($packageService);
            $this->dm->flush();
            $this->dm->clear();
            // dump($service);
        }

        $output->writeln('packages-----------------------------------------------------');
        foreach ($broken as $key => $value) {
            $output->writeln($key . '. ' . $value->getId());
        }
        $time = $start->diff(new \DateTime());
        $output->writeln('Migration complete. Elapsed time: ' . $time->format('%H:%I:%S') . '. Packages: ' . $count);
    }
}
