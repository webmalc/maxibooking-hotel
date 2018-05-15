<?php


namespace MBH\Bundle\SearchBundle\Services\Calc\Calculator;


abstract class AbstractPriceCalculator implements PriceCalculatorInterface
{
    /** @var PriceCalculatorInterface */
    protected $wrapped;

    /**
     * AbstractPriceCalculator constructor.
     * @param PriceCalculatorInterface $wrapped
     */
    public function __construct(PriceCalculatorInterface $wrapped)
    {
        $this->wrapped = $wrapped;
    }


}