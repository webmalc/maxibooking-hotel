<?php
/**
 * Created by PhpStorm.
 * User: mb3
 * Date: 06.04.18
 * Time: 13:12
 */

namespace Tests\Bundle\BaseBundle\Service;


use MBH\Bundle\BaseBundle\Lib\Test\UnitTestCase;
use MBH\Bundle\BaseBundle\Service\Address;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\PackageBundle\Lib\AddressInterface;
use Symfony\Component\DependencyInjection\Container;

class AddressTest extends UnitTestCase
{
    private const SERVICE_ID_ADDRESS = 'mbh.address';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Address
     */
    private $address;

    public function setUp()
    {
        parent::setUp();

        $this->containreWithBilling();

        $this->address = $this->container->get(self::SERVICE_ID_ADDRESS);
    }


    public function testService()
    {
        $this->assertInstanceOf(Address::class,$this->address);
    }

    public function testMethodConvertToStr()
    {
        $city = $this->address->getImperialCityStr($this->classForTest());
        $street = $this->address->getImperialStreetStr($this->classForTest());

        $this->assertEquals('Sin City, Twilight Zone, 192005', $city);
        $this->assertEquals('404, Lenin st.', $street);
    }

    /**
     * @return AddressInterface
     */
    private function classForTest(): AddressInterface
    {
        return new class() implements AddressInterface {
            public function getCityId()
            {
                return '123';
            }
            public function getName()
            {
                return null;
            }
            public function getStreet()
            {
                return 'Lenin st.';
            }
            public function getCorpus()
            {
                return null;
            }
            public function getHouse()
            {
                return '404';
            }
            public function getCountryTld()
            {
                return null;
            }
            public function getFlat()
            {
                return null;
            }
            public function getRegionId()
            {
                return '123';
            }
            public function getZipCode()
            {
                return '192005';
            }
        };
    }

    private function containreWithBilling()
    {
        self::bootKernel();
        $this->container = self::getContainerStat();

        $this->container->set('mbh.billing.api', $this->getMockBilling());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockBilling()
    {
        $service = $this->getMockBuilder(BillingApi::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCityById', 'getRegionById'])
            ->getMock();
        $service->expects($this->any())
            ->method('getCityById')
            ->will($this->returnValue(new class(){public function getName(){return 'Sin City';}}));
        $service->expects($this->any())
            ->method('getRegionById')
            ->will($this->returnValue(new class(){public function getName(){return 'Twilight Zone';}}));

        return $service;
    }
}