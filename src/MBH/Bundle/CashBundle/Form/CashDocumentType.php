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
                ->add('total', 'text', [
                    'label' => 'Сумма',
                    'required' => true,
                    'group' => $options['groupName'],
                    'attr' => ['class' => 'spinner'],
                ])
                ->add('method', 'choice', [
                    'label' => 'Способ оплаты',
                    'required' => true,
                    'multiple' => false,
                    'empty_value' => '',
                    'group' => $options['groupName'],
                    'choices' => $options['methods']
                ])
                ->add('operation', 'choice', [
                    'label' => 'Вид операции',
                    'required' => true,
                    'multiple' => false,
                    'empty_value' => '',
                    'group' => $options['groupName'],
                    'choices' => $options['operations']
                ])
                ->add('note', 'textarea', [
                    'label' => 'Комментарий',
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
            'groupName' => null
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_cashbundle_cashdocumenttype';
    }

}
