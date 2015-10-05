<?php


namespace MBH\Bundle\PackageBundle\Tests\Component;

use MBH\Bundle\PackageBundle\Component\RoomTypeReport;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        $yesterday = new \DateTime('yesterday');
        $now = new \DateTime('midnight');
        $tomorrow = new \DateTime('tomorrow');


        $package = $this->getPackage();
        $package->expects($this->any())->method('getBegin')->willReturn($now);
        $package->expects($this->any())->method('getEnd')->willReturn($tomorrow);
        $this->assertEquals($package->getRoomStatus(), Package::ROOM_STATUS_OPEN);

        $package = $this->getPackage();
        $package->expects($this->any())->method('getBegin')->willReturn($now);
        $package->expects($this->any())->method('getEnd')->willReturn($now);
        $package->expects($this->any())->method('getIsCheckIn')->willReturn(true);
        $package->expects($this->any())->method('getIsPaid')->willReturn(true);
        $this->assertEquals($package->getRoomStatus(), Package::ROOM_STATUS_OUT_NOW);

        $package = $this->getPackage();
        $package->expects($this->any())->method('getBegin')->willReturn($yesterday);
        $package->expects($this->any())->method('getEnd')->willReturn($tomorrow);
        $package->expects($this->any())->method('getIsCheckIn')->willReturn(true);
        $package->expects($this->any())->method('getIsPaid')->willReturn(true);
        $this->assertEquals($package->getRoomStatus(), Package::ROOM_STATUS_PAID);

        $package = $this->getPackage();
        $package->expects($this->any())->method('getBegin')->willReturn($yesterday);
        $package->expects($this->any())->method('getEnd')->willReturn($tomorrow);
        $package->expects($this->any())->method('getIsCheckIn')->willReturn(true);
        $package->expects($this->any())->method('getIsPaid')->willReturn(false);
        $this->assertEquals($package->getRoomStatus(), Package::ROOM_STATUS_DEPT);

        $package = $this->getPackage();
        $package->expects($this->any())->method('getBegin')->willReturn($yesterday);
        $package->expects($this->any())->method('getEnd')->willReturn($yesterday);
        $package->expects($this->any())->method('getIsCheckIn')->willReturn(true);
        $package->expects($this->any())->method('getIsCheckOut')->willReturn(false);
        $this->assertEquals($package->getRoomStatus(), Package::ROOM_STATUS_NOT_OUT);
    }

    /**
     * @return Package
     */
    private function getPackage()
    {
        $package = $this->getMock(Package::class);
        $package->expects($this->any())->method('getOrder')->willReturn(new Order());

        return clone($package);
    }
}
