<?php

namespace MBH\Bundle\CashBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CashDocumentValidator extends ConstraintValidator
{

    /**
     * @param \MBH\Bundle\CashBundle\Document\CashDocument $cashDocument
     * @param Constraint $constraint
     * @return bool
     */
    public function validate($cashDocument, Constraint $constraint)
    {
        $order = $cashDocument->getOrder();

        /** payer **/
        if (!$cashDocument->getPayer(true)) {
            $this->context->addViolation($constraint->messagePayer);
        }
        /** expenseGreaterThanIncome */
        if ($cashDocument->getOperation() == 'out') {
            if ($cashDocument->getTotal() >  $order->getPaid()) {
                $this->context->addViolation($constraint->messageExpenseGreaterThanIncome, ['%total%' => (float) $order->getPaid()]);
            }
        }

        return true;
    }

}
