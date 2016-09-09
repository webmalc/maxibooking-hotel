<?php

namespace MBH\Bundle\RestaurantBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TableValidator extends ConstraintValidator
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    const AMOUNT_CHAIRS = 19;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    /**
     * @param \MBH\Bundle\RestaurantBundle\Document\Table $table
     * @param Constraint $constraint
     * @return bool
     */
    public function validate($table, Constraint $constraint)
    {

        if ( count($table->getChairs()->toArray()) > self::AMOUNT_CHAIRS ) {
            $this->context->addViolation($constraint->messageError);
        }
            return true;
    }

}
