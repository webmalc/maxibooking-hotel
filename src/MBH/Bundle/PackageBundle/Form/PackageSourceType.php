<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PackageSourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', 'text', [
                    'label' => 'Назавание',
                    'group' => 'Добавить источник',
                    'required' => true,
                    'attr' => ['placeholder' => 'Реклама']
                ])
                ->add('title', 'text', [
                    'label' => 'Внутреннее название',
                    'group' => 'Добавить источник',
                    'required' => false,
                    'attr' => ['placeholder' => 'Название для использования внутри MaxiBooking']
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PackageBundle\Document\PackageSource',
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_packagepackagesourecetype';
    }

}
