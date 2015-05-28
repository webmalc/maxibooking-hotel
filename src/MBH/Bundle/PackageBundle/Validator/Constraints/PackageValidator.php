<?php

namespace MBH\Bundle\PackageBundle\Validator\Constraints;

use MBH\Bundle\PackageBundle\Document\Package;
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

    /**
     * @param Package $package
     * @param Constraint $constraint
     * @return bool
     */
    public function validate($package, Constraint $constraint)
    {
        if($package->getBegin() >= $package->getEnd()) {
            $this->context->addViolation($constraint->beginEndMessage);
        }
        if ($package->getRoomType()->getTotalPlaces() < ($package->getAdults() + $package->getChildren())) {
            $this->context->addViolation($constraint->placesMessage);
        }
        if ($package->getIsCheckOut() && !$package->getIsCheckIn()) {
            $this->context->addViolation($constraint->checkOutMessage);
        }
        if (($package->getIsCheckOut() || $package->getIsCheckIn()) && !$package->getAccommodation()) {
            $this->context->addViolation($constraint->checkInOutMessage);
        }

        return true;
    }

}
