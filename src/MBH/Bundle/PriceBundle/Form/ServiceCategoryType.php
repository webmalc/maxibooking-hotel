<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ServiceCategoryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', 'text', [
                    'label' => 'Название',
                    'required' => true,
                    'attr' => ['placeholder' => 'Основные услуги']
                ])
                ->add('title', 'text', [
                    'label' => 'Внутреннее название',
                    'required' => false,
                    'attr' => ['placeholder' => 'Основные услуги - лето ' . date('Y')],
                    'help' => 'Название для использования внутри MaxiBooking'
                ])
                ->add('description', 'textarea', [
                    'label' => 'Описание',
                    'required' => false,
                    'help' => 'Описание категории услуг для онлайн бронирования'
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\ServiceCategory'
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_service_category_type';
    }

}
