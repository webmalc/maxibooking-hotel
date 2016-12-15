<?php
namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class TaskData

 */
class PackageData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->persistPackage($manager);
    }

    public function getTariff(ObjectManager $manager, Hotel $hotel)
    {
//        $lastTariff = $manager->getRepository('MBHPriceBundle:Tariff')->getLastTariff();
//var_dump($lastTariff);
//        if($lastTariff) {
//            return $lastTariff;
//        }

        $tariff = new Tariff();

        $tariff->setFullTitle("Основной тариф2")
            ->setIsDefault(true)
            ->setIsOnline(true)
            ->setHotel($hotel)
        ->setMinPerPrepay(25);
        $manager->persist($tariff);
        $manager->flush();

        return $tariff;
    }

    public function persistPackageSource(ObjectManager $manager)
    {

//        $lastPackageSource = $manager->getRepository('MBHPackageBundle:PackageSource')->getLastPackageSource();
//
//        if($lastPackageSource) {
//            return $lastPackageSource;
//        }

        $packageSource = new PackageSource();
        $packageSource->setFullTitle('ОстровОк');
        $packageSource->setSystem('true');
        $packageSource->setCode('OstrovOk');
        $packageSource->setIsEnabled('true');

        $manager->persist($packageSource);
        $manager->flush();

        return $packageSource;
    }

    public function persistOrder(ObjectManager $manager, $data)
    {
        $order = new Order();

        $order
            ->setPaid($data['paid'])
            ->setStatus('offline')
            ->setTotalOverwrite($data['price'])
            ->setSource($this->persistPackageSource($manager));

        $manager->persist($order);
        $manager->flush();

        return $order;
    }

    public function persistHotel(ObjectManager $manager)
    {
        //$lastHotel = $manager->getRepository('MBHHotelBundle:Hotel')->getLastHotel();
var_dump($this->getReference('hotel-one'));
//        if($lastHotel) {
//            return $lastHotel;
//        }

        $hotel = new Hotel();
        $hotel->setFullTitle('Новый отель');

        $manager->persist($hotel);
        $manager->flush();

        return $hotel;
    }

    public function persistRoomType(ObjectManager $manager)
    {
        $lastRoomType = $manager->getRepository('MBHHotelBundle:RoomType')->getLastRoomType();

        //var_dump($lastRoomType);

        if($lastRoomType) {
            return $lastRoomType;
        }

        $roomType = new RoomType();
        $roomType
            ->setFullTitle('Двуместный')
            ->setColor('008000')
            ->setPlaces(2)
            ->setAdditionalPlaces(1);

        $manager->persist($roomType);
        $manager->flush();

        return $roomType;
    }

    public function getData()
    {
        return [
            [ 'adults' => '1', 'number' => '1', 'children' => '0', 'price' => '2000.0', 'paid' => '2001'],
            [ 'adults' => '1', 'number' => '2', 'children' => '0', 'price' => '800.0', 'paid' => '900'],
            [ 'adults' => '1', 'number' => '3', 'children' => '0', 'price' => '7000.0', 'paid' => '1000'],
            [ 'adults' => '1', 'number' => '4', 'children' => '0', 'price' => '3946.0', 'paid' => '50'],
        ];
    }

    public function persistPackage(ObjectManager $manager)
    {
        $hotel = $this->persistHotel($manager);
        $tariff = $this->getTariff($manager, $hotel);
        $roomType = $this->persistRoomType($manager);
        $date = new \DateTime();

        foreach ($this->getData() as $data) {
            $order = $this->persistOrder($manager, $data);

            $package = new Package();
            $package
                ->setAdults($data['adults'])
                ->setNumber($data['number'])
                ->setChildren($data['children'])
                ->setPrice($data['price'])
                ->setOrder($order)
                ->setTariff($tariff)
                ->setRoomType($roomType)
                ->setBegin($date)
                ->setEnd($date);

            $manager->persist($package);
            $manager->flush();
        }
    }

    public function getOrder()
    {
        return -9991;
    }
}