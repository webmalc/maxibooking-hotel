<?php

namespace MBH\Bundle\PriceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TariffValidator extends ConstraintValidator
{

    /**
     * @param \MBH\Bundle\PriceBundle\Document\Tariff $tariff
     * @param Constraint $constraint
     * @return bool
     */
    public function validate($tariff, Constraint $constraint)
    {
        if ($tariff->getInfantAge() > $tariff->getChildAge()) {
            $this->context->buildViolation($constraint->messageAges)->atPath('infantAge')->addViolation();
        }

        $begin = $tariff->getBegin();
        $end = $tariff->getEnd();

        if (!$begin instanceof \DateTimeInterface || !$end instanceof \DateTimeInterface) {
            return;
        }

        if ($tariff->getBegin() > $tariff->getEnd()) {
            $this->context->buildViolation($constraint->messageDates)->atPath('end')->addViolation();
        }

        return true;
    }

}
