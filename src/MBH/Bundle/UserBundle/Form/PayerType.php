<?php

namespace MBH\Bundle\UserBundle\Form;

use MBH\Bundle\BillingBundle\Lib\Model\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country', TextType::class, [
                'group' => 'form.payer_type.country_group',
                'label' => 'form.payer_type.country.label',
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'countries'
                ]
            ])
            ->add('payerType', ChoiceType::class, [
                'group' => 'form.payer_type.payer_type_group',
                'choices' => [
                    'юр. лцо' => 1
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
//            'data_class' => Client::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhuser_bundle_payer_type';
    }
}
