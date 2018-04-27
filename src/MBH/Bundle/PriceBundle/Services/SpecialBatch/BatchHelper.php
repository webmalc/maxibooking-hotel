<?php


namespace MBH\Bundle\PriceBundle\Services\SpecialBatch;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Lib\SpecialBatcherException;
use MBH\Bundle\PriceBundle\Lib\SpecialBatchHolder;

class BatchHelper implements SpecialBatchInterface
{
    /** @var DocumentManager */
    private $dm;

    /** @var string */
    private $purposeGetMethod;

    /** @var string */
    private $purposeSetMethod;

    /**
     * Batch constructor.
     * @param DocumentManager $dm
     * @param string $purposeGet
     * @param string $purposeSet
     */
    public function __construct(DocumentManager $dm, string $purposeGet, string $purposeSet)
    {
        $this->dm = $dm;
        $this->purposeGetMethod = $purposeGet;
        $this->purposeSetMethod = $purposeSet;
    }

    /**
     * @param SpecialBatchHolder $holder
     * @throws SpecialBatcherException
     */
    public function applyBatch(SpecialBatchHolder $holder): void
    {
        $specials = $holder->getSpecials();
        $purpose = $holder->{$this->purposeGetMethod}();

        if (!$purpose || !is_iterable($specials)) {
            throw new SpecialBatcherException('There is no specials or promotion in holder.');
        }

        foreach ($specials as $special) {
            $special->{$this->purposeSetMethod}($purpose);
            try {
                $this->dm->persist($purpose);
            } catch (\Exception $e) {
                throw new SpecialBatcherException('Error in doctrine persist');
            }
        }

        try {
            $this->dm->flush();
        } catch (\Exception $e) {
            throw new SpecialBatcherException('Error in doctrine flush method');
        }
    }

}