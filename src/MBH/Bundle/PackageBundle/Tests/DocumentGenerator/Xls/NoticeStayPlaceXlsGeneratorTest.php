<?php

namespace MBH\Bundle\PackageBundle\Tests\DocumentGenerator\Xls;


use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\PackageBundle\Document\Migration;
use MBH\Bundle\PackageBundle\Document\Visa;
use MBH\Bundle\PackageBundle\DocumentGenerator\Xls\NoticeStayPlaceXlsGenerator;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NoticeStayPlaceXlsGeneratorTest

 */
class NoticeStayPlaceXlsGeneratorTest extends WebTestCase
{
    /**
     * @var NoticeStayPlaceXlsGenerator
     */
    private $generator;

    public function setUp()
    {
        self::$kernel->boot();
        $container = self::$kernel->getContainer();
        $this->generator = new NoticeStayPlaceXlsGenerator();
        $this->generator->setContainer($container);
    }

    /**
     * Ger formData fixture
     * @return array
     */
    private function getFormData()
    {
        $package = new Package();
        $roomType = new RoomType();
        $hotel = new Hotel();
        $roomType->setHotel($hotel);
        $package->setRoomType($roomType);
        $package->setBegin(new \DateTime('- 1 day'));
        $package->setEnd(new \DateTime('+ 1 day'));

        $tourist = new Tourist();
        $user = new User();
        $migration = new Migration();
        $tourist->setMigration($migration);
        $visa = new Visa();
        $tourist->setVisa($visa);


        return [
            'package' => $package,
            'tourist' => $tourist,
            'user' => $user
        ];
    }

    public function testGenerateResponse()
    {
        $formData = $this->getFormData();
        $response = $this->generator->generateResponse($formData);
        $this->assertTrue($response instanceof Response);
    }
}
