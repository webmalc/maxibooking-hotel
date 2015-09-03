<?php


namespace MBH\Bundle\PackageBundle\Tests\Component;

use MBH\Bundle\PackageBundle\Component\ReportRoomTypeStatus;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ReportRoomTypeStatusTest
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class ReportRoomTypeStatusTest extends WebTestCase
{
    /**
     * @var ReportRoomTypeStatus
     */
    protected $roomTypeStatus;

    public function setUp()
    {
        $this->roomTypeStatus = new ReportRoomTypeStatus();
    }

    public function testGetStatusByPackage()
    {
        $yesterday = new \DateTime('yesterday');
        $now = new \DateTime('midnight');
        $tomorrow = new \DateTime('tomorrow');

        $package = $this->getPackage();
        $package->expects($this->any())->method('getBegin')->willReturn($now);
        $package->expects($this->any())->method('getEnd')->willReturn($tomorrow);
        $this->assertEquals($this->roomTypeStatus->getStatusByPackage($package), ReportRoomTypeStatus::OPEN);

        $package = $this->getPackage();
        $package->expects($this->any())->method('getBegin')->willReturn($now);
        $package->expects($this->any())->method('getEnd')->willReturn($now);
        $package->expects($this->any())->method('getIsCheckIn')->willReturn(true);
        $package->expects($this->any())->method('getIsPaid')->willReturn(true);
        $this->assertEquals($this->roomTypeStatus->getStatusByPackage($package), ReportRoomTypeStatus::OUT_NOW);


        $package = $this->getPackage();
        $package->expects($this->any())->method('getBegin')->willReturn($yesterday);
        $package->expects($this->any())->method('getEnd')->willReturn($tomorrow);
        $package->expects($this->any())->method('getIsCheckIn')->willReturn(true);
        $package->expects($this->any())->method('getIsPaid')->willReturn(true);
        $this->assertEquals($this->roomTypeStatus->getStatusByPackage($package), ReportRoomTypeStatus::PAID);

        $package = $this->getPackage();
        $package->expects($this->any())->method('getBegin')->willReturn($yesterday);
        $package->expects($this->any())->method('getEnd')->willReturn($tomorrow);
        $package->expects($this->any())->method('getIsCheckIn')->willReturn(true);
        $package->expects($this->any())->method('getIsPaid')->willReturn(false);
        $this->assertEquals($this->roomTypeStatus->getStatusByPackage($package), ReportRoomTypeStatus::DEPT);

        $package = $this->getPackage();
        $package->expects($this->any())->method('getBegin')->willReturn($yesterday);
        $package->expects($this->any())->method('getEnd')->willReturn($yesterday);
        $package->expects($this->any())->method('getIsCheckIn')->willReturn(true);
        $package->expects($this->any())->method('getIsCheckOut')->willReturn(false);
        $this->assertEquals($this->roomTypeStatus->getStatusByPackage($package), ReportRoomTypeStatus::NOT_OUT);
    }

    private function getPackage()
    {
        $package = $this->getMock(Package::class);
        $package->expects($this->any())->method('getOrder')->willReturn(new Order());

        return clone($package);
    }
}
