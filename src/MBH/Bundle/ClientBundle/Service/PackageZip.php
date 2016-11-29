<?php
namespace MBH\Bundle\ClientBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\RestaurantBundle\Document\Chair;
use MBH\Bundle\RestaurantBundle\Document\Table;
use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

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

    public function __construct(DocumentManager $dm, ContainerInterface $container)
    {
        $this->dm = $dm;
        $this->container = $container;
        $this->validator = $container->get('validator');
        $this->logger = $container->get('mbh.packagezip.logger');
    }

    public function packagesZip()
    {
        $this->logger->alert('---------START---------');
        $roomTypeZipConfig = $this->dm->getRepository('MBHClientBundle:RoomTypeZip')->fetchConfig();
        $clientConfig = $roomTypeZipConfig->getClientConfig();
        $categoriesId = null;

        $categories = $roomTypeZipConfig->getCategories()->toArray();

        foreach ($categories as $category) {
            $categoriesId[] = $category->getId();
        }
        $roomTypesByCategories = $this->dm->getRepository('MBHHotelBundle:RoomType')->roomByCategories($roomTypeZipConfig->getHotel(), $categoriesId);
        //date update RoomTypes
        $begin = new \DateTime('-1 day');
        $end = new \DateTime('+365 day');

        $packages = $this->dm->getRepository('MBHPackageBundle:Package')->getPackageCategory($begin, $end, $roomTypesByCategories ? array_keys($roomTypesByCategories->toArray()) : null);

        foreach ($packages as $package) {
            $countPersons = (int)$package->getAdults() + (int)$package->getChildren();
            $countRoom = $package->getRoomType()->getTotalPlaces();

            if ($countPersons < $countRoom) {

                $this->logger->alert('---------BEGIN Package---------');
                $beginLog = clone $package->getBegin();
                $endLog = clone $package->getEnd();

                $this->logger->info('BEGIN PACKAGE INFO', [
                    'Begin' => $beginLog->format('d-m-Y'),
                    'End' => $endLog->format('d-m-Y'),
                    'id' => $package->getId(),
                    'RoomType_id' => $package->getRoomType()->getId(),
                    'Tariff_id' => $package->getTariff()->getId(),
                    'CreatedBy' => $package->getCreatedBy(),
                    'NumberWithPrefix' => $package->getNumberWithPrefix()
                ]);

                $categoryRoomType[] = $package->getRoomType()->getCategory()->getId();
                $roomTypesByCategory = $this->dm->getRepository('MBHHotelBundle:RoomType')->roomByCategories($roomTypeZipConfig->getHotel(), $categoryRoomType);

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

                $groupedResult = $this->container->get('mbh.package.search')
                    ->search($query);

                foreach ($groupedResult as $resultSearch) {

                    $countRoomToSearch = $resultSearch->getRoomType()->getTotalPlaces();
                    if (!isset($pick)) {
                        $pick = $resultSearch;
                    } elseif (isset($pick) && ($countRoomToSearch < $pick->getRoomType()->getTotalPlaces())) {
                        $pick = $resultSearch;
                    }

                }

                $oldPackage = clone $package;
                $newPackage = clone $package;
                $newPackage->setRoomtype($pick->getRoomType());
                $endDate = clone $package->getEnd();

                $result = $this->container->get('mbh.order_manager')->updatePackage($oldPackage, $newPackage);

                if ($result instanceof Package) {

                    $package->setEnd($endDate)
                        ->setRoomtype($pick->getRoomType());

                    $this->dm->persist($package);

                    $beginLog2 = clone $package->getBegin();
                    $endLog2 = clone $package->getEnd();
                    $this->logger->info('CHANGED PACKAGE INFO', [
                        'Begin' => $beginLog2->format('d-m-Y'),
                        'End' => $endLog2->format('d-m-Y'),
                        'id' => $package->getId(),
                        'RoomType_id' => $package->getRoomType()->getId(),
                        'Tariff_id' => $package->getTariff()->getId(),
                        'CreatedBy' => $package->getCreatedBy(),
                        'NumberWithPrefix' => $package->getNumberWithPrefix()
                    ]);

                }

            }
        }
        $this->logger->alert('---------END---------');
        $this->dm->flush();
    }

}