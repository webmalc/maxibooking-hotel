<?php

namespace MBH\Bundle\PackageBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PackageValidator extends ConstraintValidator
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function validate($object, Constraint $constraint)
    {
        if($object->getBegin() >= $object->getEnd()) {
            $this->context->addViolation($constraint->beginEndMessage);
        }
        
        /*$tariff = $object->getTariff();
        if($tariff && ($object->getBegin() < $tariff->getBegin() || $object->getBegin() > $tariff->getEnd() || $object->getEnd() < $tariff->getBegin() || $object->getEnd() > $tariff->getEnd())) {
            $this->context->addViolation($constraint->tariffMessage);
        }*/

        return true;
    }

}
