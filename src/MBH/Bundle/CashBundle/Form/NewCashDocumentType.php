<?php

namespace MBH\Bundle\CashBundle\Form;

use MBH\Bundle\CashBundle\Form\Extension\PayerType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class NewCashDocumentType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class NewCashDocumentType extends CashDocumentType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $payerType = new PayerType();
        $payerType->buildForm($builder, $options + ['ajax' => true]);
        //$builder->add('type_payer', $payerType, ['mapped' => false]);
    }
}