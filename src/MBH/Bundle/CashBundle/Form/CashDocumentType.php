<?php

namespace MBH\Bundle\CashBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CashDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('payer_select', 'text', [
                    'label' => 'form.cashDocumentType.payer',
                    'required' => false,
                    'mapped' => false,
                    'data' => (empty($options['payer'])) ? null : $options['payer']->getId(),
                    'group' => $options['groupName'],
                    'attr' => ['placeholder' => 'form.cashDocumentType.placeholder_fio', 'style' => 'min-width: 500px']
                ])
                ->add('total', 'text', [
                    'label' => 'form.cashDocumentType.sum',
                    'required' => true,
                    'group' => $options['groupName'],
                    'attr' => ['class' => 'spinner'],
                ])
                ->add('method', 'choice', [
                    'label' => 'form.cashDocumentType.payment_way',
                    'required' => true,
                    'multiple' => false,
                    'empty_value' => '',
                    'group' => $options['groupName'],
                    'choices' => $options['methods']
                ])
                ->add('operation', 'choice', [
                    'label' => 'form.cashDocumentType.operation_type',
                    'required' => true,
                    'multiple' => false,
                    'empty_value' => '',
                    'group' => $options['groupName'],
                    'choices' => $options['operations']
                ])
                ->add('note', 'textarea', [
                    'label' => 'form.cashDocumentType.comment',
                    'group' => $options['groupName'],
                    'required' => false,
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\CashBundle\Document\CashDocument',
            'methods' => [],
            'operations' => [],
            'groupName' => null,
            'payer' => null
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_cashbundle_cashdocumenttype';
    }

}
