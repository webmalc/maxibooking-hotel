<?php


namespace MBH\Bundle\PriceBundle\DataFixtures\MongoDB;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Tariff;

class AdditionalPromotionData extends AbstractFixture implements OrderedFixtureInterface
{
    public function doLoad(ObjectManager $manager)
    {
        $tariff1 = $this->getReference(AdditionalTariffData::UP_TARIFF_NAME . '-tariff' . '/1');
        $tariff2 = $this->getReference(AdditionalTariffData::DOWN_TARIFF_NAME . '-tariff' . '/1');

        $promotion1 = new Promotion();
        $promotion1
            ->setFullTitle('FirstPromotion')
            ->setDiscount(30)
            ->setIsPercentDiscount(true)
        ;

        $promotion2 = new Promotion();
        $promotion2
            ->setFullTitle('SecondPromotion')
            ->setDiscount(50)
            ->setIsPercentDiscount(true)
        ;
        /** @var Tariff $tariff1 */
        $tariff1->setDefaultPromotion($promotion1);
        /** @var Tariff $tariff2 */
        $tariff2->setDefaultPromotion($promotion2);

        $manager->persist($promotion1);
        $manager->persist($promotion2);

        $manager->flush();

    }


    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }

    public function getOrder()
    {
        return 200;
    }


}