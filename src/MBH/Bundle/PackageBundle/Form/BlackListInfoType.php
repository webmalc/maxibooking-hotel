<?php

namespace MBH\Bundle\PackageBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TouristBlackListType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class BlackListInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('aggressor', 'checkbox', [
            'label' => 'form.blackListInfoType.aggressor'
        ]);

        $builder->add('comment', 'textarea', [
            'label' => 'form.blackListInfoType.comment'
        ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\BlackListInfo'
        ]);
    }


    public function getName()
    {
        return 'mbh_package_bundle_tourist_black_list';
    }
}