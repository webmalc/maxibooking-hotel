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
        $hotel = $this->getReference(HotelData::HOTELS_DATA_KEY_ONE);

        $formConfig = new PaymentFormConfig();
        $formConfig
            ->setHotels([$hotel])
            ->setEnabledReCaptcha(false)
            ->setFieldUserNameIsVisible(false)
            ->setForMbSite(false)
            ->setIsFullWidth(true);

        $manager->persist($formConfig);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }

    public function getOrder()
    {
       return 10000;
    }


}