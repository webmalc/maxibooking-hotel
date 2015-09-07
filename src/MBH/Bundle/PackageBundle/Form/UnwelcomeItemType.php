<?php

namespace MBH\Bundle\PackageBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UnwelcomeItemType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class UnwelcomeItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('isAggressor', 'checkbox', [
            'label' => 'form.blackListInfoType.aggressor',
            'required' => false
        ]);

        $builder->add('comment', 'textarea', [
            'label' => 'form.blackListInfoType.comment'
        ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\UnwelcomeItem'
        ]);
    }


    public function getName()
    {
        return 'mbh_package_bundle_unwelcome_item';
    }
}