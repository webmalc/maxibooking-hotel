<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentTypesType extends AbstractType
{
    private $paymentTypes;

    public function __construct(array $onlineFormConfig) {
        $this->paymentTypes = array_keys($onlineFormConfig['payment_types']);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_label' => function ($value) {
                return 'payment_types.' . $value;
            },
            'label' => 'form.formType.payment_type',
            'choices' => $this->paymentTypes,
            'multiple' => true
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhonline_bundle_payment_types_type';
    }
}