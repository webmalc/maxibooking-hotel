<?php

namespace MBH\Bundle\PackageBundle\Validator\Constraints;

use MBH\Bundle\PriceBundle\Lib\SpecialFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
     * @param \MBH\Bundle\PackageBundle\Document\Package $package
     * @param Constraint $constraint
     */
    public function validate($package, Constraint $constraint)
    {
        $begin = $package->getBegin();
        $end = $package->getEnd();

        if ($begin === null || $end === null) {
            return;
        }

        if($begin >= $end) {
            $this->context->addViolation($constraint->beginEndMessage);
        }

        $roomType = $package->getRoomType();
        $guest = $package->getAdults() + $package->getChildren();

        if ($roomType === null || (int) $guest === 0) {
            return;
        }

        if ($roomType->getTotalPlaces() < $guest) {
            $this->context->addViolation($constraint->placesMessage);
        }
        if ($package->getIsCheckOut() && !$package->getIsCheckIn()) {
            $this->context->addViolation($constraint->checkOutMessage);
        }

        $special = $package->getSpecial();

        if ($special) {
            $dm = $this->container->get('doctrine_mongodb')->getManager();
            $filter = new SpecialFilter();
            $filter->setHotel($package->getHotel())
                ->setRoomType($package->getRoomType())
                ->setBegin($package->getBegin())
                ->setEnd($package->getEnd())
                ->setTariff($package->getTariff())
                ->setRemain(1)
                ->setExcludeSpecial($special)
            ;

            $specials = $dm->getRepository('MBHPriceBundle:Special')->getFiltered($filter);

            if (!in_array($special, $specials->toArray())) {
                $this->context->addViolation($constraint->specialMessage);
            }

            $packages = $dm->getRepository('MBHPackageBundle:Package')
                ->getBuilderBySpecial($special)
                ->getQuery()->execute();

            if ($special->getRemain() < 1 && !in_array($package, $packages->toArray())) {
                $this->context->addViolation($constraint->specialMessage);
            }
        }
    }
}
