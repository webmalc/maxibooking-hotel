<?php
/**
 * Created by PhpStorm.
 * Date: 03.12.18
 */

namespace MBH\Bundle\OnlineBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\HotelBundle\DataFixtures\MongoDB\HotelData;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;

class PaymentFormData extends AbstractFixture implements OrderedFixtureInterface
{
    public function doLoad(ObjectManager $manager)
    {
        $formConfig = new PaymentFormConfig();

        if ($this->getEnv() === 'test') {
            $hotel = $this->getReference(HotelData::HOTELS_DATA_KEY_ONE);

            $formConfig
                ->setHotels([$hotel])
                ->setEnabledReCaptcha(false)
                ->setFieldUserNameIsVisible(false)
                ->setForMbSite(false)
                ->setIsFullWidth(true);
        } else {
            $formConfig->setForMbSite(true);
        }

        $manager->persist($formConfig);

        $manager->flush();
    }

    public function getOrder()
    {
       return 10000;
    }


}
