<?php
namespace MBH\Bundle\PackageBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class OrderData
 */
class OrderData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->persistPackage($manager);
    }

    public function persistPackageSource(ObjectManager $manager)
    {

        $packageSource = new PackageSource();
        $packageSource
            ->setFullTitle('ОстровОк')
            ->setSystem(true)
            ->setCode('Ostrovok')
        ->setIsEnabled(true);


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
            ->setSource($this->persistPackageSource($manager))
            ->setCreatedAt((new \DateTime())->modify("-{$data['regDayAgo']} day"));

        $manager->persist($order);
        $manager->flush();

        return $order;
    }

    public function getData()
    {
        return [
            [ 'adults' => '1', 'number' => '1', 'children' => '0', 'price' => '2000.0', 'paid' => '2001', 'regDayAgo' => '1'],
            [ 'adults' => '1', 'number' => '2', 'children' => '0', 'price' => '800.0', 'paid' => '10', 'regDayAgo' => '10'],
            [ 'adults' => '1', 'number' => '3', 'children' => '0', 'price' => '7000.0', 'paid' => '1000', 'regDayAgo' => '12'],
            [ 'adults' => '1', 'number' => '4', 'children' => '0', 'price' => '4631.0', 'paid' => '276', 'regDayAgo' => '2'],
            [ 'adults' => '1', 'number' => '4', 'children' => '0', 'price' => '8000.0', 'paid' => '8000', 'regDayAgo' => '5'],
            [ 'adults' => '1', 'number' => '4', 'children' => '0', 'price' => '9364.0', 'paid' => '10', 'regDayAgo' => '118'],
            [ 'adults' => '1', 'number' => '4', 'children' => '0', 'price' => '430.0', 'paid' => '560', 'regDayAgo' => '17'],
            [ 'adults' => '1', 'number' => '4', 'children' => '0', 'price' => '9999.0', 'paid' => '1000', 'regDayAgo' => '15'],
            [ 'adults' => '1', 'number' => '4', 'children' => '0', 'price' => '7000.0', 'paid' => '50', 'regDayAgo' => '0'],
        ];
    }

    public function persistPackage(ObjectManager $manager)
    {
        $tariff = $this->getReference('my-tariff');
        $roomType = $this->getReference('roomtype-double');
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