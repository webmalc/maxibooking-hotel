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
        if ($object->getRoomType()->getTotalPlaces() < ($object->getAdults() + $object->getChildren())) {
            $this->context->addViolation($constraint->placesMessage);
        }

        return true;
    }

}
