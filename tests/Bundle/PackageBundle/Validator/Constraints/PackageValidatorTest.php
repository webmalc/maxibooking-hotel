<?php

use MBH\Bundle\PackageBundle\Validator\Constraints\PackageValidator;

/**
 * Created by PhpStorm.
 * Date: 18.06.18
 */

class PackageValidatorTest extends \MBH\Bundle\BaseBundle\Lib\Test\ValidatorTestCase
{
    private $container;

    public function setUp()
    {
        $this->container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);

        parent::setUp();
    }

    protected function createValidator(): \Symfony\Component\Validator\ConstraintValidatorInterface
    {
        return new PackageValidator($this->container);
    }

    /**
     * @param $package
     * @dataProvider getNullIsValid
     */
    public function testNullIsValid($package)
    {
        $this->validator->validate($package, new \MBH\Bundle\PackageBundle\Validator\Constraints\Package());

        $this->assertNoViolation();
    }

    /**
     * @param $package
     * @dataProvider getInvalidDataWithoutSpacial
     */
    public function testInvalidDataWithoutSpecial($package)
    {
        $this->validator->validate($package, new \MBH\Bundle\PackageBundle\Validator\Constraints\Package());

        $this->assertYesViolation();
    }

    /**
     * @param $data
     * @dataProvider getInvalidDataWithSpecial
     */
    public function testInvalidDataWithSpecial($data)
    {
        $this->setUp();

        self::bootKernel();
        $container = static::$kernel->getContainer();
        $container->set('doctrine_mongodb', $data['manager']);

        $this->validator = new PackageValidator($container);

        $this->validator->initialize($this->context);

        $this->validator->validate($data['package'], new \MBH\Bundle\PackageBundle\Validator\Constraints\Package());

        $this->assertYesViolation();
    }

    public function getInvalidDataWithSpecial(): array
    {
        $special_1 = new \MBH\Bundle\PriceBundle\Document\Special();
        $special_1->setRemain(2);
        $package_1 = $this->getValidPackageWithoutSpecial();
        $package_1->setSpecial($special_1);

        $special_2 = new \MBH\Bundle\PriceBundle\Document\Special();
        $special_2->setRemain(0);
        $package_2 = $this->getValidPackageWithoutSpecial();
        $package_2->setSpecial($special_2);

        return [
            [['manager' => $this->getInvalidManagerRegistry(1), 'package' => $package_1]],
            [['manager' => $this->getInvalidManagerRegistry(2), 'package' => $package_2]],
        ];
    }

    public function getNullIsValid(): array
    {
        $package_1 = new \MBH\Bundle\PackageBundle\Document\Package();

        $package_2 = clone $package_1;
        $package_2->setBegin(new DateTime());
        $package_2->setEnd(new DateTime());

        $package_3 = clone $package_2;
        $package_3->setRoomType(new \MBH\Bundle\HotelBundle\Document\RoomType());

        return [
            [$package_1],
            [$package_2],
            [$package_3],
        ];
    }

    public function getInvalidDataWithoutSpacial(): array
    {
        $package_1 = new \MBH\Bundle\PackageBundle\Document\Package();
        $package_1->setBegin(new DateTime('+10 days'));
        $package_1->setEnd(new DateTime());

        $roomType = $this->getValidRoomType();

        $package_2 = new \MBH\Bundle\PackageBundle\Document\Package();
        $package_2->setBegin(new DateTime());
        $package_2->setEnd(new DateTime());
        $package_2->setRoomType($roomType);
        $package_2->setAdults(10);
        $package_2->setChildren(10);

        $package_3 = new \MBH\Bundle\PackageBundle\Document\Package();
        $package_3->setBegin(new DateTime());
        $package_3->setEnd(new DateTime());
        $package_3->setRoomType($roomType);
        $package_3->setAdults(1);
        $package_3->setChildren(1);
        $package_3->setIsCheckOut(true);
        $package_3->setIsCheckIn(false);

        return [
            [$package_1],
            [$package_2],
            [$package_3],
        ];
    }

    /**
     * @return \MBH\Bundle\PackageBundle\Document\Package
     */
    private function getValidPackageWithoutSpecial(): \MBH\Bundle\PackageBundle\Document\Package
    {
        $roomType = $this->getValidRoomType();

        $package = new \MBH\Bundle\PackageBundle\Document\Package();
        $package->setBegin(new DateTime());
        $package->setEnd(new DateTime());
        $package->setRoomType($roomType);
        $package->setAdults(1);
        $package->setChildren(1);
        $package->setIsCheckOut(false);
        $package->setIsCheckIn(true);

        $package->setTariff(new \MBH\Bundle\PriceBundle\Document\Tariff());

        return $package;
    }

    /**
     * @return \MBH\Bundle\HotelBundle\Document\RoomType
     */
    private function getValidRoomType(): \MBH\Bundle\HotelBundle\Document\RoomType
    {
        $roomType = new \MBH\Bundle\HotelBundle\Document\RoomType();
        $roomType->setPlaces(10);
        $roomType->setHotel(new \MBH\Bundle\HotelBundle\Document\Hotel());

        return $roomType;
    }

    /**
     * @param int $invalidIteration
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getInvalidManagerRegistry(int $invalidIteration)
    {
        $mockMR = $this->getMockBuilder(\Doctrine\Bundle\MongoDBBundle\ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getManager'])
            ->getMock();

        $mockMR->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->getMockDocumentManager($invalidIteration)));

        return $mockMR;
    }

    /**
     * @param int $invalidIteration
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockDocumentManager(int $invalidIteration)
    {
        $mockDM = $this->getMockBuilder(\Stubs\DocumentManager::class)
            ->setMethods(['getRepository'])
            ->getMock();

        switch ($invalidIteration){
            case 1:
                $mockDM->expects($this->any())
                    ->method('getRepository')
                    ->will($this->returnCallback(function ($param) {
                        if ($param == 'MBHPriceBundle:Special') {
                            return new class()
                            {
                                public function getFiltered($data)
                                {
                                    return $this;
                                }

                                public function toArray()
                                {
                                    return [];
                                }
                            };
                        } else {
                            return new class()
                            {
                                public function getBuilderBySpecial($data)
                                {
                                    return $this;
                                }

                                public function getQuery()
                                {
                                    return $this;
                                }

                                public function execute()
                                {
                                    return $this;
                                }

                                public function toArray()
                                {
                                    return [new \MBH\Bundle\PackageBundle\Document\Package()];
                                }
                            };
                        }
                    }
                    ));
                break;
            case 2:
                $mockDM->expects($this->any())
                    ->method('getRepository')
                    ->will($this->returnCallback(function ($param) {
                        if ($param == 'MBHPriceBundle:Special') {
                            return new class()
                            {
                                public function getFiltered($data)
                                {
                                    return $this;
                                }

                                public function toArray()
                                {
                                    return [new \MBH\Bundle\PriceBundle\Document\Special()];
                                }
                            };
                        } else {
                            return new class()
                            {
                                public function getBuilderBySpecial($data)
                                {
                                    return $this;
                                }

                                public function getQuery()
                                {
                                    return $this;
                                }

                                public function execute()
                                {
                                    return $this;
                                }

                                public function toArray()
                                {
                                    return [];
                                }
                            };
                        }
                    }
                    ));
                break;
        }

        return $mockDM;
    }
}