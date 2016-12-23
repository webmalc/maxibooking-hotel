<?php

namespace MBH\Bundle\PackageBundle\Validator\Constraints;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PackageAccommodationsValidator extends ConstraintValidator
{
    private $dm;

    private $translator;

    /**
     * PackageAccommodationValidator constructor.
     * @param $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }


    public function validate($packageAccommodation, Constraint $constraint)
    {
        /** @var PackageAccommodation $packageAccommodation */
        $package = $packageAccommodation->getPackage();
        //Check PackageAccommodation by Package
        if ($packageAccommodation->getBegin() < $package->getBegin() || $packageAccommodation->getEnd() > $package->getEnd()) {
            $this->context->buildViolation($constraint->wrongStartOrEndMessage)
                ->addViolation()
            ;
        }
        //Check intersect PakageAccommodation by neighbors
        /** @var PackageAccommodation $packageAccommodation */
        $accommodations = $package->getAccommodations();
        foreach ($accommodations as $accommodation) {
            /** @var PackageAccommodation $accommodation */
            if ($packageAccommodation->getEnd() > $accommodation->getBegin() && $packageAccommodation->getBegin() < $accommodation->getEnd()) {
                $this->context->buildViolation($constraint->intersectNeighbour, ['%neighbourId%' => $accommodation->getId()])
                    ->addViolation()
                ;
            }
        }

        //Check globaly is accommodation intersect
        $existAccommodations = $this->dm
            ->getRepository('MBHPackageBundle:PackageAccommodation')
            ->createQueryBuilder()
            ->field('accommodation.id')->equals($packageAccommodation->getAccommodation()->getId())
            ->field('begin')->lt($packageAccommodation->getEnd())
            ->field('end')->gt($packageAccommodation->getBegin())
            ->field('id')->notEqual($packageAccommodation->getId())
            ->getQuery()
            ->execute();
        $existAccId = '';
        if ($existAccommodations->count()) {
            foreach ($existAccommodations as $existAccommodation) {
                $existAccId .= ' ' . $existAccommodation->getId();
            }
            $this->context->buildViolation($constraint->intersectNeighbour, ['%neighbourId%' => $existAccId])
                ->addViolation()
            ;
        }
    }

}