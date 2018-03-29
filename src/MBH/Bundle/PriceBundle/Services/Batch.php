<?php


namespace MBH\Bundle\PriceBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Lib\SpecialBatcherException;
use MBH\Bundle\PriceBundle\Lib\SpecialBatcherHolder;

class Batch
{
    /** @var DocumentManager */
    private $dm;

    /**
     * Batch constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @param SpecialBatcherHolder $holder
     * @throws SpecialBatcherException
     */
    public function batchPromotionToSpecialApply(SpecialBatcherHolder $holder): void
    {
        $specials = $holder->getSpecials();
        $promotion = $holder->getPromotion();

        if (!is_iterable($specials) || !$promotion) {
            throw new SpecialBatcherException('There is no specials or promotion in holder.');
        }

        foreach ($specials as $special) {
            if ($special instanceof Special) {
                $special->setPromotion($promotion);
                $this->dm->persist($promotion);
            }
        }

        $this->dm->flush();
    }
}