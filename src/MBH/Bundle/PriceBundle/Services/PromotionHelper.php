<?php

namespace MBH\Bundle\PriceBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber\DocumentsRelationships;
use MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber\Relationship;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Tariff;

class PromotionHelper
{
    /**@var Helper */
    protected $helper;

    /**@var DocumentManager */
    protected $dm;

    /**
     * @param Helper $helper
     * @param DocumentManager $dm
     */
    public function __construct(Helper $helper, DocumentManager $dm)
    {
        $this->helper = $helper;
        $this->dm = $dm;
    }

    /**
     * @param Promotion $promotion
     * @return bool
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function tryToHandlePromotionRelations(Promotion $promotion): bool
    {
        $relationships = DocumentsRelationships::getRelationships()[Promotion::class];

        /** @var Relationship $relationship */
        foreach ($relationships as $relationship) {
            if (($relationship->getDocumentClass() === Package::class) && $this->checkPackagesPromotion($promotion)) {
                return false;
            }
            if ($relationship->getDocumentClass() === Tariff::class) {
                $this->deleteTariffsPromotion($promotion);
            }
        }

        return true;
    }

    /**
     * @param Promotion $promotion
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    protected function deleteTariffsPromotion(Promotion $promotion): void
    {
        $tariffs = $this->dm->getRepository(Tariff::class)->createQueryBuilder()
            ->field('promotions')->exists(true)
            ->field('deletedAt')->exists(false)
            ->getQuery()->execute();

        /** @var Tariff $tariff */
        foreach ($tariffs as $tariff) {
            $tariffPromotions = $tariff->getPromotions()->toArray();
            if (false !== $key = array_search($promotion, $tariffPromotions)) {
                unset($tariffPromotions[$key]);
                sort($tariffPromotions);
                $tariff->setPromotions($tariffPromotions);
            }
        }
        $this->dm->flush();
    }

    /**
     * @param Promotion $promotion
     * @return bool
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    protected function checkPackagesPromotion(Promotion $promotion): bool
    {
        $packages = $this->dm->getRepository(Package::class)->createQueryBuilder()
            ->field('promotion')->exists(true)
            ->field('deletedAt')->exists(false)
            ->getQuery()->execute();

        /** @var Package $package */
        foreach ($packages as $package) {
            if ($package->getPromotion() === $promotion) {
                return true;
            }
        }

        return false;
    }
}
