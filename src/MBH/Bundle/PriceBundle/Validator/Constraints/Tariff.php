<?php
namespace MBH\Bundle\PriceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Tariff extends Constraint
{
    public $message = 'Интервал основного тарифа не должен пересекается с другими основными тарифами. <ul>%tariffs%</ul>';
    
    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
        return 'mbh.tariff.validator';
    }
}