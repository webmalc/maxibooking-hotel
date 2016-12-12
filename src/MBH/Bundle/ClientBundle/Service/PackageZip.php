<?php
namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use MBH\Bundle\PackageBundle\Services\OrderManager;
use MBH\Bundle\PackageBundle\Services\Search\SearchFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PackageZip
{
    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Component\Validator\Validator;
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var SearchFactory
     */
    private $packageSearch;

    /**
     * @var Helper
     */
    private $helper;

    const BEGIN = 'midnight';

    const END = '+365 days';

    public function __construct(DocumentManager $dm, ContainerInterface $container, ValidatorInterface $validator, LoggerInterface $logger, OrderManager $orderManager, SearchFactory $packageSearch, Helper $helper)
    {
        $this->dm = $dm;
        $this->container = $container;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->orderManager = $orderManager;
        $this->packageSearch = $packageSearch;
        $this->helper = $helper;
    }

    public function packagesZip()
    {
        $this->logger->alert('---------START---------');
        $roomTypeZipConfig = $this->dm->getRepository('MBHClientBundle:RoomTypeZip')->fetchConfig();
        $roomTypesByCategories = $this->roomTypeByCategories($roomTypeZipConfig);

        $skip = 50;
        $info['amount'] = 0;
        $info['error'] = 0;

        for ($i = 0; $i <= ceil($this->getMaxAmountPackages($roomTypesByCategories)/$skip); $i++) {

            try {

                $packages = $this->getPackages($roomTypesByCategories, $skip, $i);

                foreach ($packages as $package) {

                    $countPersons = $package->getCountPersons();
                    $countRoom = $package->getRoomType()->getTotalPlaces();

                    if ($countPersons < $countRoom) {
                        $this->logger->alert('---------BEGIN Package---------');
                        $beginLog = clone $package->getBegin();
                        $endLog = clone $package->getEnd();

                        $this->LogPackage('BEGIN PACKAGE INFO', $beginLog, $endLog, $package);

                        $categoryRoomType[] = $package->getRoomType()->getCategory()->getId();
                        $roomTypesByCategory = $this->dm->getRepository('MBHHotelBundle:RoomType')->roomByCategories($roomTypeZipConfig->getHotel(), $categoryRoomType);

                        //query Search
                        $query = new SearchQuery();

                        foreach ($roomTypesByCategory as $roomTypeCategory) {
                            if ($roomTypeCategory->getTotalPlaces() < $countRoom) {
                                $query->roomTypes[] = $roomTypeCategory->getId();
                            }
                        }

                        $query->begin = clone $package->getBegin();
                        $query->end = clone $package->getEnd();
                        $query->adults = $package->getAdults();
                        $query->children = $package->getChildren();
                        $query->forceRoomTypes = true;

                        $groupedResult = $this->packageSearch->search($query);

                        if (!$groupedResult) {
                            continue;
                        }

                        usort($groupedResult, function ($a, $b) {
                            if ($a->getRoomType()->getTotalPlaces() == $b->getRoomType()->getTotalPlaces()) {
                                return 0;
                            }
                            return ($a->getRoomType()->getTotalPlaces() < $b->getRoomType()->getTotalPlaces()) ? -1 : 1;
                        });

                        $oldPackage = clone $package;
                        $newPackage = clone $package;
                        $newPackage->setRoomtype($groupedResult[0]->getRoomType());
                        $overTotalPrice = $package->getPrice();
                        $servicePrice = $package->getServicesPrice();
                        $endDate = clone $package->getEnd();

                        $result = $this->orderManager->updatePackage($oldPackage, $newPackage);

                        if ($result instanceof Package && !($package->getRoomType()->getId() == $newPackage->getRoomType()->getId()) ) {

                            $info['amount']++;
                            $package->setEnd($endDate)
                                ->setServicesPrice($servicePrice)
                                ->setTotalOverwrite($overTotalPrice)
                                ->setRoomtype($groupedResult[0]->getRoomType());

                            $this->dm->persist($package);

                            $beginLog2 = clone $package->getBegin();
                            $endLog2 = clone $package->getEnd();
                            $this->LogPackage('CHANGED PACKAGE INFO', $beginLog2, $endLog2, $package);

                        }

                    }
                }
                $this->dm->flush();
                $this->dm->clear();

            } catch (\Exception $e) {
                $info['error']++ ;
                $this->LogPackage('ERROR: ' . $e->getMessage(), $package->getBegin(), $package->getEnd(), $package);
            }

        }
        $this->logger->alert('Final TOTAL: ' . $info['amount'] . "\n");
        $this->logger->alert('Final ERROR: ' . $info['error'] . "\n");
        $this->logger->alert('---------END---------');

        return $info;
    }

    protected function getMaxAmountPackages($roomTypesByCategories)
    {
        return $this->dm->getRepository('MBHPackageBundle:Package')->getPackageCategory(
                new \DateTime(self::BEGIN),
                new \DateTime(self::END),
                $roomTypesByCategories ? array_keys($roomTypesByCategories->toArray()) : null,
                true
        );
    }

    protected function getPackages($roomTypesByCategories = null, $skip, $count)
    {

        return $this
            ->dm
            ->getRepository('MBHPackageBundle:Package')
            ->getPackageCategory(
                new \DateTime(self::BEGIN),
                new \DateTime(self::END),
                $roomTypesByCategories ? array_keys($roomTypesByCategories->toArray()) : null,
                false,
                true,
                $skip * $count
            );

    }

    protected function roomTypeByCategories($config)
    {

        return $this->dm->getRepository('MBHHotelBundle:RoomType')->roomByCategories($config->getHotel(), $this->helper->toIds($config->getCategories()));

    }

    protected function LogPackage($message, $begin, $end, $package)
    {
        $this->logger->info($message, [
            'Begin' => $begin->format('d-m-Y'),
            'End' => $end->format('d-m-Y'),
            'id' => $package->getId(),
            'RoomType_id' => $package->getRoomType()->getId(),
            'Tariff_id' => $package->getTariff()->getId(),
            'CreatedBy' => $package->getCreatedBy(),
            'NumberWithPrefix' => $package->getNumberWithPrefix(),
            'Price' => $package->getPrice(),
            'TotalPrice' => $package->getTotalOverwrite()
        ]);
    }

}