<?php

namespace MBH\Bundle\DemoBundle\Command;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\RoomPrice;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixturesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:demo:load')
            ->setDescription('Load data in project from scripts/upload/example.demo.json')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'file name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (empty($input->getOption('name'))) {
            $output->writeln("<error>--name = false</error>");
            return false;
        }

        $name = $input->getOption('name');
        $path = $this->getContainer()->get('kernel')->getRootDir() . '/../scripts/upload/' . $name . '.demo.json';

        if (!file_exists($path) || !is_readable($path)) {
            $output->writeln('Error. New data not loaded. File not exist!!!');
            return false;
        }

        $hotelInfo = json_decode(file_get_contents($path), 1);

        if (empty($hotelInfo) || !is_array($hotelInfo)) {
            $output->writeln('Error. New data not loaded. File not json!!!');
            return false;
        }

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $from = new \DateTime();
        $from->setTime(0, 0, 0);
        $to = clone $from;
        $to->modify('+3 months');
        $colors = ['008000', 'ff6600', '0000ff'];
        $isHostel = ($hotelInfo['hotel_type'] == 'hostel') ? true : false;

        // Hotel
        $hotel = new Hotel();
        $hotel->setFullTitle($hotelInfo['hotel_name'])
              ->setIsDefault(true)
              ->setPrefix('HTL')
              ->setSaleDays(365)
              ->setCreatedBy('demo')
              ->setIsHostel($isHostel)
        ;
        $numberInfo = $hotelInfo['hotel_numbertype'];

        $dm->persist($hotel);

        // Tariff
        $tariff = new Tariff();

        $tariff->setCreatedBy('demo')
               ->setHotel($hotel)
               ->setTitle('Основной тариф')
               ->setIsDefault(true)
               ->setBegin($from)
               ->setEnd($to)
               ->setIsOnline(true)
        ;

        $packages = [];
        $withoutAccommodation = false;

        $desc[] = <<<EOF
Однокомнатный комфортабельный номер, площадью 20 кв.м. В номере одна широкая французская кровать (категории «double») или две раздельные (категории «twin»).

Данный номер идеально подходит для одноместного или же двуместного размещения. При необходимости в номер можно поставить кроватку для ребенка.
EOF;

        $desc[] = <<<EOF
Отель располагает номерами "Комфорт", оборудованными современной техникой. Номер "Комфорт" оснащен отоплением, кондиционером, телефоном, сейфом, феном, скоростным интернетом, спутниковым телевидением, ванной комнатой, минибаром, балконом.

В номере:Номера для четырех человек. Может быть установлена детская кроватка (без дополнительной оплаты).
EOF;

        $desc[] = <<<EOF
Номер категории "Люкс" - по-домашнему уютный двухкомнатный номер, площадью 52 кв. м. Номер состоит из комфортной спальни с большой французской кроватью и гостиной, в которой есть уютный диванчик, письменный стол, стул. Интерьер гостиной – потрясающий пример сочетания цветов, гармонии линий и форм, которые придают номеру неповторимый колорит.

Освещение номера приятным светом порадует своей легкостью и непринуждённостью.

Как в спальне, так и в гостиной есть телевизор. Диван в гостиной можно использовать как спальное место.

Номер категории "Люкс" порадует гостей своим сочетанием изысканности и подбору деталей.
EOF;




        foreach($numberInfo['name'] as $key => $roomTypeName) {

            //RoomType
            $roomType = new RoomType();
            $roomType->setFullTitle($roomTypeName)
                     ->setColor($colors[$key])
                     ->setCalculationType(($isHostel) ? 'customPrices' : 'perRoom')
                     ->setHotel($hotel)
                     ->setAdditionalPlaces(0)
                     ->setPlaces($numberInfo['acc'][$key])
                     ->setCreatedBy('demo')
                    ->setImage(($key + 1 ) . '.jpeg')
                    ->setDescription($desc[$key])

            ;

            $dm->persist($roomType);

            //RoomTypePrice
            $roomPrice = new RoomPrice();
            $roomPrice->setAdditionalAdultPrice(0)
                      ->setAdditionalChildPrice(0)
                      ->setRoomType($roomType)
                      ->setPrice($numberInfo['price'][$key])
            ;
            $tariff->addRoomPrice($roomPrice);

            $rooms = [];
            //Rooms
            for ($i = 1; $i <= $numberInfo['count'][$key]; $i++) {
                $room = new Room();

                ($isHostel) ? $roomName =  $roomType->getFullTitle() . '/' .sprintf("%02d", $i) : $roomName = sprintf("%02d", $i);

                $room->setCreatedBy('demo')
                    ->setHotel($hotel)
                    ->setRoomType($roomType)
                    ->setTitle($roomName)
                ;

                $dm->persist($room);
                $rooms[] = $room;
            }

            for ($i = 1; $i <=3; $i++) {

                $pRoom = false;
                if (count($rooms)) {
                    $pRoom = $rooms[$i - 1];
                }

                //Packages
                $begin = clone $from;
                $begin->modify('+' . rand(1,25) . ' day');
                $end = clone $begin;
                $end->modify('+' . rand(3,25) . ' day');

                $package = new Package();
                $number = count($packages) + 1;
                $package->setCreatedBy('demo')
                    ->setBegin($begin)
                    ->setEnd($end)
                    ->setRoomType($roomType)
                    ->setAdults(1)
                    ->setChildren(0)
                    ->setIsPaid(false)
                    ->setNote('Демонстрационные брони')
                    ->setNumber($number)
                    ->setStatus('offline')
                    ->setPaid(0)
                    ->setNumberWithPrefix('HTL' . $number)
                ;
                if ($withoutAccommodation || ($i != 2 && $pRoom)) {
                    $package->setAccommodation($pRoom);
                } else {
                    $withoutAccommodation = true;
                }

                $package->setPrice($numberInfo['price'][$key] * $package->getNights());
                $packages[] = $package;
            }
        }

        $dm->persist($tariff);
        $dm->flush();

        //Add tourist
        $tourist  = new Tourist();
        $tourist->setFirstName('Иван')
                ->setLastName('Иванов')
                ->setPatronymic('Иванович')
                ->setSex('male')
                ->setBirthday(\DateTime::createFromFormat('d.m.Y', '16.05.1968'))
                ->setPhone('+79251234567')
                ->setEmail('example@example.com')
        ;

        $dm->persist($tourist);
        $dm->flush();

        foreach ($packages as $package) {
            $package->setTariff($tariff)
                ->setMainTourist($tourist)
                ->addTourist($tourist)
            ;
            $dm->persist($package);
        }

        $dm->flush();

        //Online form
        $formConfig = new FormConfig();
        $formConfig->setEnabled(true)
                   ->setNights(true)
                   ->setRoomTypes(true)
                   ->setTourists(true)
                   ->setPaymentTypes(["in_hotel", "online_full", "online_first_day"])
        ;

        $dm->persist($formConfig);
        $dm->flush();

        unlink($path);

        $output->writeln('Complete. New data loaded.');
    }
}