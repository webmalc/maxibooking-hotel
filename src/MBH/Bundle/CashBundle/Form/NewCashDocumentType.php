<?php

namespace MBH\Bundle\CashBundle\Form;

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

        $builder
            ->add('payer_select', 'choice', [
                'label' => 'form.cashDocumentType.payer',
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'form.cashDocumentType.placeholder_fio',
                    'style' => 'min-width: 500px',
                    'class' => 'payer-select plain-html'
                ],
                'empty_value' => '',
            ]);
    }
}