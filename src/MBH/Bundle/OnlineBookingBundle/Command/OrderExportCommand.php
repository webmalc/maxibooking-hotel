<?php


namespace MBH\Bundle\OnlineBookingBundle\Command;

use Doctrine\MongoDB\Connection;
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
use MBH\Bundle\PackageBundle\Document\PackageSource;
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
        return $this->getContainer()->get('file_locator')->locate('@MBHOnlineBookingBundle/Resources/data/FullReportsTourists.csv');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = new \DateTime();
        /** @var DocumentManager $dm */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        /** @var Helper $helper */
        $helper = $this->getContainer()->get('mbh.helper');

        /** @var Connection $connection */
        $connection = $this->getContainer()->get('doctrine_mongodb')->getConnection();
        $connection->getConfiguration()->setLoggerCallable(null);

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

        $rowNum=0;
        while (($data = fgetcsv($resource, null, ",")) !== false) {
            $output->writeln('<info>Row #' . $rowNum. '</info>');
            $rowNum++;
            if (count($data) < 34) {
                continue;
            }

            $data = array_map('trim', $data);
            $index = $data[0];
            $number = $data[1];

            if ($dm->getRepository('MBHPackageBundle:Package')->findOneBy(
                ['$or' => [['externalNumber' => $number], ['numberWithPrefix' => $number]]])
            ) {
                continue;
            }

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
            $sourceTitle = $data[42];

            $order = new Order();
            $order->setStatus('offline');
            $order->setIsEnabled(true);
            $order->setTotalOverwrite($finalTotal);
            $order->setPrice($finalTotal);
            $order->setConfirmed(true);

            $tourist = null;
            if ($fio) {
                // list ($lastName, $firstName, $patronymic) = explode(' ', trim($fio));
                $fioData = explode(' ', trim($fio));
                $lastName = $fioData[0];
                $firstName = $fioData[1];
                $patronymic = isset($fioData[2]) ? $fioData[2] : null;


                $tourist = $touristRepository->createQueryBuilder()
                    ->field('firstName')->equals($firstName)
                    ->field('lastName')->equals($lastName)
                    ->field('patronymic')->equals($patronymic)
                    ->limit(1)
                    ->getQuery()
                    ->getSingleResult();
                if (!$tourist) {
                    $tourist = new Tourist();
                    $tourist->setFirstName($firstName);
                    $tourist->setLastName($lastName);
                    $tourist->setPatronymic($patronymic)->setCreatedBy('import');
                    $dm->persist($tourist);
                    $dm->flush();
                    //$tourist = $touristRepository->fetchOrCreate($lastName, $firstName, $patronymic);
                }

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
                if ($lastName && $firstName) {
                    $user = $userRepository->findOneBy(['firstName' => $firstName, 'lastName' => $lastName]);
                    if (!$user) {
                        $user = new User();
                        $user->setFirstName($firstName);
                        $user->setLastName($lastName);
                        $user->setPlainPassword('12345');
                        $user->setUsername(Helper::translateToLat($firstName . '_' . $lastName))->setCreatedBy('import');
                        $dm->persist($user);
                        $dm->flush();
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
            $package->setTotalOverwrite($finalTotal);
            $package->setPrice($finalTotal);
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
                    $hotelTitle = 'Пансионат Азовский';
                    break;
                case 2:
                    $hotelTitle = 'Пансионат АзовЛенд';
                    break;
                case 3:
                    $hotelTitle = 'Парк - отель "РИО"';
                    break;
                default:
                    throw new \Exception('hotel is not defined');
                    break;
            }

            $hotel = $hotelRepository->findOneBy(['title' => $hotelTitle]);
            if (!$hotel) {
                $hotel = new Hotel();
                $hotel->setTitle($hotelTitle)->setFullTitle($hotelTitle)->setCreatedBy('import');
                //$hotelManager->create($hotel);
                $dm->persist($hotel);
                $dm->flush();
            }

            if ($hotel->getId()) {
                $roomTypeCategory = $roomTypeCategoryRepository->findOneBy([
                    'title' => $roomTypeCategoryTitle,
                    'hotel.id' => $hotel->getId()
                ]);
                if (!$roomTypeCategory) {
                    $roomTypeCategory = new RoomTypeCategory();
                    $roomTypeCategory->setTitle($roomTypeCategoryTitle)->setFullTitle($roomTypeCategoryTitle);
                    $roomTypeCategory->setHotel($hotel)->setCreatedBy('import');
                    $dm->persist($roomTypeCategory);
                    $output->writeln('Добавлена новая категория "'. $roomTypeCategory->getTitle() . '"');
                }

                $roomType = $roomTypeRepository->findOneBy(['title' => $roomTypeName, 'hotel.id' => $hotel->getId()]);
                if (!$roomType) {
                    $roomType = new RoomType();
                    $roomType->setHotel($hotel);
                    $roomType->setTitle($roomTypeName)->setFullTitle($roomTypeName);
                    $roomType->setCategory($roomTypeCategory)->setCreatedBy('import');
                    $dm->persist($roomType);
                    $output->writeln('Добавлен новый тип номера "'. $roomTypeCategory->getTitle() . '". Не забудте добавить количесво мест.');
                }

                $package->setRoomType($roomType);
            }

            $dm->persist($order);
            $dm->persist($package);
            $dm->flush();
            $dm->clear($order);
            if(isset($cashDocument)) {
                $dm->clear($cashDocument);
            }

            $packages[] = $package;


            $hotelManager->updateFixture($hotel);
            $baseTariff = $tariffRepository->fetchBaseTariff($hotel);
            if ($baseTariff) {
                $package->setTariff($baseTariff);
            }
            $dm->flush();

            //Source
            $source = $dm->getRepository('MBHPackageBundle:PackageSource')->findOneBy(['title' => $sourceTitle]);
            if (!$source) {
                $source = new PackageSource();
                $source
                    ->setTitle($sourceTitle)
                    ->setFullTitle($sourceTitle)
                    ->setCreatedBy('import')
                    ->setIsEnabled(true)
                ;
                $dm->persist($source);
                $dm->flush();
            }
            $order->setSource($source);


            /*if ($rowNum > 10) {
                break;
            }*/
        }

        /*foreach($packages as $package) {
            $dm->persist($package);
        }*/

        $endTime = new \DateTime();

        $output->writeln('Done. Time: ' . $endTime->diff($startTime)->format('%H:%I:%S'));
    }

}