<?php

namespace MBH\Bundle\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                    'form.payer_type.legal_entity' => 1,
                    'form.payer_type.natural_entity' => 2
                ],
                'label' => 'form.payer_type.label'
            ])
            ->add('address', TextType::class, [
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.address.label'
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'cities'
                ],
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.city.label'
            ])
            ->add('state', TextType::class, [
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.state.label'
            ])
            ->add('postalCode', IntegerType::class, [
                'group' => 'form.payer_type.address_group',
                'label' => 'form.payer_type.postal_code.label'
            ])
            ->add('documentType', TextType::class, [
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.document_type.label'
            ])
            ->add('series', TextType::class, [
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.series.label'
            ])
            ->add('number', TextType::class, [
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.number.label'
            ])
            ->add('issueDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['class' => 'datepicker begin-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'],
                'constraints' => [new NotBlank(), new Date()],
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.issue_date.label'
            ])
            ->add('issuedBy', TextType::class, [
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'fms'
                ],
                'group' => 'form.payer_type.identification_group',
                'label' => 'form.payer_type.issue_by.label'
            ])
            ->add('inn', TextType::class, [
                'group' => 'form.payer_type.financial_information.label',
                'label' => 'form.payer_type.inn.label'
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
