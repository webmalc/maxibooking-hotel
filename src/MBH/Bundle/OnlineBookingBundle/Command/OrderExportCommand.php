<?php


namespace MBH\Bundle\OnlineBookingBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategoryRepository;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\HotelBundle\Service\HotelManager;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageRepository;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Document\TouristRepository;
use MBH\Bundle\PriceBundle\Document\PackageInfo;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\RoomCacheRepository;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OrderExportCommand
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class OrderExportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('azovsky:order:export')
            ->setDescription('Order export');
    }

    protected function getPathTouristsCsv()
    {
        return $this->getContainer()->get('file_locator')->locate('@MBHOnlineBookingBundle/Resources/data/FullReportsTourists(1).csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DocumentManager $dm */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        /** @var Helper $helper */
        $helper = $this->getContainer()->get('mbh.helper');

        /** @var TouristRepository $touristRepository */
        $touristRepository = $dm->getRepository(Tourist::class);
        /** @var DocumentRepository $hotelRepository */
        $hotelRepository = $dm->getRepository(Hotel::class);
        /** @var RoomTypeCategoryRepository $roomTypeCategoryRepository */
        $roomTypeCategoryRepository = $dm->getRepository(RoomTypeCategory::class);
        /** @var RoomTypeRepository $roomTypeRepository */
        $roomTypeRepository = $dm->getRepository(RoomType::class);
        /** @var HotelManager $hotelManager */
        $hotelManager = $this->getContainer()->get('mbh.hotel.hotel_manager');

        $path = $this->getPathTouristsCsv();
        $resource = fopen($path, 'r');
        fgetcsv($resource, null, ",");

        $tariffRepository = $dm->getRepository('MBHPriceBundle:Tariff');
        $userRepository = $dm->getRepository('MBHUserBundle:User');

        $minDate = null;
        $maxDate = null;

        $packages = [];

        while (($data = fgetcsv($resource, null, ",")) !== false) {
            $data = array_map(function ($item) {
                return iconv("WINDOWS-1251", "UTF-8", $item);
            }, $data);

            if (!count($data) > 34) {
                continue;
            }


            $index = $data[0];
            $number = $data[1];
            $date = $data[2];
            $fio = $data[3];
            $total = floatval(str_replace(' ', '', $data[13]));
            $sale = $data[14];
            //$finalTotal = $data[15];
            $finalTotal = floatval(str_replace(' ', '', $data[16]));

            $duty = floatval(str_replace(' ', '', $data[17]));
            $phone = $data[19];
            $roomTypeCategoryTitle = $data[20];
            $roomTypeName = $data[21];
            $additionalPlaces = $data[23];
            $children = $data[24];
            $adults = $data[25];
            $places = $data[26];
            $arrivalTime = $data[27];
            $departureTime = $data[34];
            $manager = $data[41];

            $order = new Order();
            $order->setStatus('offline');
            $order->setIsEnabled(true);
            $order->setTotalOverwrite($finalTotal);

            $tourist = null;
            if ($fio) {
                list ($lastName, $firstName, $patronymic) = explode(' ', trim($fio));

                /*$tourist = $touristRepository->createQueryBuilder()
                    ->field('firstName')->equals($firstName)
                    ->field('lastName')->equals($lastName)
                    ->field('patronymic')->equals($patronymic)
                    ->limit(1)
                    ->getQuery()
                    ->getSingleResult();
                if (!$tourist) {*/
                $tourist = $touristRepository->fetchOrCreate($lastName, $firstName, $patronymic);
                //}
                $order->setMainTourist($tourist);
                $dm->persist($tourist);
            } else {
                //throw new \Exception('Fio is not exists');
                continue;
            }
            if (!$number) {
                continue;
            }

            $package = new Package();

            $user = null;
            if ($manager) {
                list ($lastName, $firstName) = explode(' ', trim($manager));
                if ($lastName && $finalTotal) {
                    $user = $userRepository->findOneBy(['firstName' => $firstName, 'lastName' => $lastName]);
                    if (!$user) {
                        $user = new User();
                        $user->setFirstName($firstName);
                        $user->setLastName($lastName);
                        $user->setPlainPassword('12345');
                        $user->setUsername(Helper::translateToLat($firstName . '_' . $lastName));
                        $dm->persist($user);
                    }

                    $order->setCreatedBy($user->getUsername());
                    $package->setCreatedBy($user->getUsername());
                }
            }

            $date = $helper->getDateFromString($date, 'd.m.Y h:i:s');
            if ($date) {
                $order->setCreatedAt($date);
                $package->setCreatedAt($date);
            }

            if ($duty != $finalTotal && $finalTotal - $duty) {
                $cashDocument = new CashDocument();
                $cashDocument->setTotal($finalTotal - $duty);
                $cashDocument->setIsPaid(true);
                $cashDocument->setMethod('cash');
                if ($date) {
                    $cashDocument->setCreatedAt($date);
                    $cashDocument->setDocumentDate($date);
                }
                if ($user) {
                    $cashDocument->setCreatedBy($user);
                }
                if ($tourist) {
                    $cashDocument->setTouristPayer($tourist);
                }
                $cashDocument->setIsConfirmed(true);
                $cashDocument->setOperation('in');
                $cashDocument->setIsEnabled(true);
                $cashDocument->setOrder($order);
                $dm->persist($cashDocument);
            }

            if ($duty == 0) {
                $order->setIsPaid(true);
            }

            $order->addPackage($package);
            $package->setOrder($order);
            $package->setTotalOverwrite($total);
            $package->setAdults($adults);
            $package->setChildren($children);

            $arrivalTime = $helper->getDateFromString($arrivalTime, 'd.m.Y h:i:s');
            $departureTime = $helper->getDateFromString($departureTime, 'd.m.Y h:i:s');
            if (!$arrivalTime || !$departureTime) {
                throw new \Exception($index . ' has not date');
            }
            //$package->setArrivalTime($arrivalTime);
            $package->setBegin($arrivalTime);
            //$package->setDepartureTime($departureTime);
            $package->setEnd($departureTime);

            $package->setExternalNumber($number);
            $package->setNumberWithPrefix($number);
            $order->setNote(implode(', ', $data));

            $result = [];
            preg_match("/^([1-3]{1})-([0-9]+)/i", $number, $result);
            $prefix = intval($result[1]);
            //$number = intval($result[2]);
            $hotelTitle = null;
            switch ($prefix) {
                case 1:
                    $hotelTitle = 'Азовский';
                    break;
                case 2:
                    $hotelTitle = 'АзовЛенд';
                    break;
                case 3:
                    $hotelTitle = 'РИО';
                    break;
                default:
                    throw new \Exception('hotel is not defined');
                    break;
            }

            $hotel = $hotelRepository->findOneBy(['title' => $hotelTitle]);
            if (!$hotel) {
                $hotel = new Hotel();
                $hotel->setTitle($hotelTitle);
                $hotelManager->create($hotel);
            }


            $roomTypeCategory = $roomTypeCategoryRepository->findOneBy([
                'title' => $roomTypeCategoryTitle,
                'hotel.id' => $hotel->getId()
            ]);
            if (!$roomTypeCategory) {
                $roomTypeCategory = new RoomTypeCategory();
                $roomTypeCategory->setTitle($roomTypeCategoryTitle);
                $roomTypeCategory->setHotel($hotel);
                $dm->persist($roomTypeCategory);
            }

            $roomType = $roomTypeRepository->findOneBy(['title' => $roomTypeName, 'hotel.id' => $hotel->getId()]);
            if (!$roomType) {
                $roomType = new RoomType();
                $roomType->setHotel($hotel);
                $roomType->setTitle($roomTypeName);
                $roomType->setCategory($roomTypeCategory);
                $dm->persist($roomType);
            }

            $package->setRoomType($roomType);

            $baseTariff = $tariffRepository->fetchBaseTariff($hotel);
            $package->setTariff($baseTariff);

            $dm->persist($order);
            $dm->persist($package);
            $dm->flush();
            $dm->clear();

            $packages[] = $package;
        }

        $this->recountRoomCache($packages);

        $output->writeln('Done');
    }


    /**
     * @param Package[] $packages
     */
    private function recountRoomCache($packages)
    {
        $minDate = null;
        $maxDate = null;
        foreach ($packages as $package) {
            if (!$minDate || $minDate > $package->getBegin()) {
                $minDate = $package->getBegin();
            }
            if ($maxDate < $package->getEnd()) {
                $maxDate = $package->getEnd();
            }
        }

        /** @var DocumentManager $dm */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        /** @var RoomCacheRepository $roomCacheRepository */
        $roomCacheRepository = $dm->getRepository(RoomCache::class);

        /** @var PackageRepository $packageRepository */
        $packageRepository = $dm->getRepository(Package::class);

        /** @var RoomCache[] $roomCaches */
        $roomCaches = $roomCacheRepository->findBy(['date' => ['$gte' => $minDate, '$lte' => $maxDate]]);


        foreach ($roomCaches as $roomCache) {
            $packageCount = 0;
            $tariff = null;
            foreach ($packages as $package) {
                if ($package->getBegin()->getTimestamp() <= $roomCache->getDate()->getTimestamp() && $package->getEnd()->getTimestamp() > $roomCache->getDate()->getTimestamp() && $package->getRoomType()->getId() == $roomCache->getRoomType()->getId()) {
                    $packageCount++;
                    $tariff = $package->getTariff();
                }
            }
            /*$packageCount = $packageRepository->createQueryBuilder()
                ->field('begin')->lte($roomCache->getDate())
                ->field('end')->gt($roomCache->getDate())
                ->field('roomType.id')->equals($roomCache->getRoomType()->getId())
                ->getQuery()->count();
            ;*/

            $roomCache->setPackagesCount($packageCount);
            $roomCache->setLeftRooms($roomCache->getTotalRooms() - $roomCache->getPackagesCount());

            $packageInfo = new PackageInfo();
            dump($tariff->getId());
            if ($tariff) {
                //$tariff = $dm->getRepository(Tariff::class)->find($tariff->getId());
                //$packageInfo->setTariff($tariff);
            }
            $packageInfo->setPackagesCount($packageCount);
            $roomCache->addPackageInfo($packageInfo);

            //$dm->persist($packageInfo);
            $dm->persist($roomCache);

            $dm->flush();
            $dm->clear();
        }
    }
}