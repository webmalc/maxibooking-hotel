<?php


namespace MBH\Bundle\PackageBundle\Tests\Component;

use MBH\Bundle\PackageBundle\Component\RoomTypeReport;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class ReportRoomTypeStatusTest
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class RoomTypeReportTest extends WebTestCase
{
    /**
     * @var RoomTypeReport
     */
    protected $roomTypeReport;

    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $this->roomTypeReport = new RoomTypeReport($kernel->getContainer());
    }

    public function testGetStatusByPackage()
    {
        $yesterday = new \DateTime('yesterday midnight');
        $today = new \DateTime('midnight');
        $tomorrow = new \DateTime('tomorrow');

        $package = new Package();
        $package
            ->setBegin($tomorrow)
            ->setEnd($tomorrow)
            ->setIsCheckIn(false)
            ->setIsCheckOut(false);
        $this->assertEquals(Package::ROOM_STATUS_OPEN, $package->getRoomStatus());

        $package->setOrder(new Order());
        $this->assertEquals(Package::ROOM_STATUS_WAIT, $package->getRoomStatus());

        $package
            ->setBegin($today)
            ->setEnd($tomorrow)
            ->setIsCheckIn(false)
            ->setIsCheckOut(false);
        $this->assertEquals(Package::ROOM_STATUS_WAIT_TODAY, $package->getRoomStatus());

        $package
            ->setBegin($today)
            ->setEnd(new \DateTime('+ 6 days'))
            ->setIsCheckIn(true)
            ->setIsCheckOut(false)
        ;
        $this->assertEquals(Package::ROOM_STATUS_IN_TODAY, $package->getRoomStatus());
        $package
            ->setBegin($yesterday)
            ->setEnd(new \DateTime('+ 6 days'))
            ->setIsCheckIn(true)
            ->setIsCheckOut(false)
        ;
        $this->assertEquals(Package::ROOM_STATUS_WILL_OUT, $package->getRoomStatus());

        $package
            ->setBegin($yesterday)
            ->setEnd($tomorrow)
            ->setIsCheckIn(true)
            ->setIsCheckOut(false)
        ;
        $this->assertEquals(Package::ROOM_STATUS_OUT_TOMORROW, $package->getRoomStatus());

        $package
            ->setBegin($yesterday)
            ->setEnd($today)
            ->setIsCheckIn(true)
            ->setIsCheckOut(false)
        ;
        $this->assertEquals(Package::ROOM_STATUS_OUT_TODAY, $package->getRoomStatus());

        $package
            ->setBegin(new \DateTime('-5 days'))
            ->setEnd(new \DateTime('-1 days'))
            ->setIsCheckIn(true)
            ->setIsCheckOut(false)
        ;
        $this->assertEquals(Package::ROOM_STATUS_NOT_OUT, $package->getRoomStatus());
    }
}
