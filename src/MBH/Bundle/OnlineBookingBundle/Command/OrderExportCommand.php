<?php


namespace MBH\Bundle\OnlineBookingBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategoryRepository;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\HotelBundle\Service\HotelManager;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Document\TouristRepository;
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
        while(($data = fgetcsv($resource, null, ",")) !== false) {
            $data = array_map(function($item){
                return iconv("WINDOWS-1251", "UTF-8", $item);
            }, $data);

            if (!count($data) > 34) {
                continue;
            }

            $index = $data[0];
            $number = $data[1];
            $date = $data[2];
            $fio = $data[3];
            $total = $data[13];
            $sale = $data[14];
            $finalTotal = $data[15];
            $duty = $data[16];
            $phone = $data[19];
            $roomTypeCategoryTitle = $data[20];
            $roomTypeName = $data[21];
            $additionalPlaces = $data[23];
            $children = $data[24];
            $adults = $data[25];
            $places = $data[26];
            $arrivalTime = $data[27];
            $departureTime = $data[34];

            $order = new Order();
            $date = $helper->getDateFromString($date, 'd.m.Y h:i:s');
            if($date) {
                $order->setCreatedAt($date);
            }
            //$fio
            list ($lastName, $firstName, $patronymic) = explode(' ', trim($fio));

            $tourist = $touristRepository->createQueryBuilder()
                ->field('firstName')->equals($firstName)
                ->field('lastName')->equals($lastName)
                ->field('patronymic')->equals($patronymic)
                ->limit(1)
                ->getQuery()
                ->getSingleResult()
            ;
            if (!$tourist) {
                continue;
            }

            $package = new Package();
            $order->addPackage($package);
            $package->setOrder($order);
            $package->setTotalOverwrite($total);
            $package->setAdults($adults);
            $package->setChildren($children);

            $arrivalTime = $helper->getDateFromString($arrivalTime, 'd.m.Y h:i:s');
            $departureTime = $helper->getDateFromString($departureTime, 'd.m.Y h:i:s');
            if(!$arrivalTime || !$departureTime) {
                dump($data);
                throw new \Exception($index . ' has not date');
            }
            //$package->setArrivalTime($arrivalTime);
            $package->setBegin($arrivalTime);
            //$package->setDepartureTime($departureTime);
            $package->setEnd($departureTime);

            $package->setExternalNumber($number);
            $order->setNote(implode(', ', $data));

            $result = [];
            preg_match("/^([1-3]{1})/i", $number, $result);
            $number = intval($result[1]);
            $hotelTitle = null;
            switch ($number) {
                case 1: $hotelTitle = 'Азовский'; break;
                case 2: $hotelTitle = 'АзовЛенд'; break;
                case 3: $hotelTitle = 'РИО'; break;
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


            $roomTypeCategory = $roomTypeCategoryRepository->findOneBy(['title' => $roomTypeCategoryTitle, 'hotel.id' => $hotel->getId()]);
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
                $dm->persist($roomType);
            }

            $dm->persist($order);
            $dm->persist($package);

            $dm->flush();
        }

        $output->writeln('Done');
    }
}