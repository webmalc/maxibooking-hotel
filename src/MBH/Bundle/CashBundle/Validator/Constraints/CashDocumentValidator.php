<?php

namespace MBH\Bundle\CashBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CashDocumentValidator extends ConstraintValidator
{

    public function validate($object, Constraint $constraint)
    {
        if ($object->getOperation() == 'out') {
            $package = $object->getPackage();

            $total = $package->getPaid();
            if ($object->getTotal() >  $total) {
                $this->context->addViolation($constraint->message, ['%total%' => $total]);
            }
        }

        return true;
    }

}
