<?php
namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Promise\Tests\Thing1;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class OrderData
 */
class OrderData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const DATA = [
        [ 'adults' => '1', 'number' => '1', 'children' => '0', 'price' => '2000.0', 'paid' => '2001', 'regDayAgo' => '1'],
        [ 'adults' => '1', 'number' => '2', 'children' => '0', 'price' => '800.0', 'paid' => '10', 'regDayAgo' => '10'],
        [ 'adults' => '1', 'number' => '3', 'children' => '0', 'price' => '7000.0', 'paid' => '1000', 'regDayAgo' => '12'],
        [ 'adults' => '1', 'number' => '4', 'children' => '0', 'price' => '4631.0', 'paid' => '276', 'regDayAgo' => '2'],
        [ 'adults' => '1', 'number' => '5', 'children' => '0', 'price' => '8000.0', 'paid' => '8000', 'regDayAgo' => '5'],
        [ 'adults' => '1', 'number' => '6', 'children' => '0', 'price' => '9364.0', 'paid' => '10', 'regDayAgo' => '118'],
        [ 'adults' => '1', 'number' => '7', 'children' => '0', 'price' => '430.0', 'paid' => '560', 'regDayAgo' => '17'],
        [ 'adults' => '1', 'number' => '8', 'children' => '0', 'price' => '3000.0', 'paid' => '750', 'regDayAgo' => '15'],
        [ 'adults' => '1', 'number' => '9', 'children' => '0', 'price' => '7000.0', 'paid' => '50', 'regDayAgo' => '0'],
    ];

    const FIRST_NAME = [
        'Сергей', 'Иван', 'Александр', 'Петр', 'Арсений'
    ];

    const LAST_NAME = [
        'Виноградов', 'Алексеев', 'Тищенко', 'Петренко', 'Всеволодов'
    ];

    const PATRONYMIC = [
        'Иванович', 'Сергеевич', 'Евгеньевич', 'Петрович', 'Александрович'
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->persistPackage($manager);
    }

    /**
     * The name generator by array
     *
     * @param $array
     * @return string
     */
    public function generator($array) : string
    {
        $random_number = rand(0,count($array)-1);

        return $array[$random_number];
    }

    public function persistTourist(ObjectManager $manager)
    {
        $tourist = new Tourist();
        $firstName = $this->generator(self::FIRST_NAME);
        $lastName = $this->generator(self::LAST_NAME);
        $patronymic = $this->generator(self::PATRONYMIC);

        $tourist
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setPatronymic($patronymic)
            ->setSex('male')
            ->setCommunicationLanguage('ru');

        $manager->persist($tourist);
        $manager->flush();

        return $tourist;
    }

    public function persistOrder(ObjectManager $manager, $data)
    {
        $order = new Order();

        $order
            ->setPaid($data['paid'])
            ->setStatus('offline')
            ->setTotalOverwrite($data['price'])
            ->setSource($this->getReference('Booking.com'))
            ->setMainTourist($this->persistTourist($manager))
            ->setCreatedAt((new \DateTime())->modify("-{$data['regDayAgo']} day"));

        $manager->persist($order);
        $manager->flush();

        return $order;
    }

    public function persistPackage(ObjectManager $manager)
    {
        /** @var Tariff $tariff */
        $tariff = $this->getReference('main-tariff');
        /** @var RoomType $roomType */
        $roomType = $this->getReference('roomtype-double');
        $date = new \DateTime();

        foreach (self::DATA as $data) {
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
                ->setCreatedAt((new \DateTime())->modify("-{$data['regDayAgo']} day"))
                ->setEnd($date);

            $manager->persist($package);
            $manager->flush();
        }
    }

    public function getOrder()
    {
        return 5;
    }
}