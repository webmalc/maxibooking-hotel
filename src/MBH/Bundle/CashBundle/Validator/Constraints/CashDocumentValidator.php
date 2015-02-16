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
            $order = $object->getOrder();

            $total = $order->getPaid();

            if ($object->getTotal() >  $total) {
                $this->context->addViolation($constraint->message, ['%total%' => (int) $total]);
            }
        }

        return true;
    }

}
