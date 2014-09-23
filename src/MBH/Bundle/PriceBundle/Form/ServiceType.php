<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ServiceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', 'text', [
                    'label' => 'Название',
                    'required' => true,
                    'attr' => ['placeholder' => 'Сейф']
                ])
                ->add('title', 'text', [
                    'label' => 'Внутреннее название',
                    'required' => false,
                    'attr' => ['placeholder' => 'Сейф - лето ' . date('Y')],
                    'help' => 'Название для использования внутри MaxiBooking'
                ])
                ->add('description', 'textarea', [
                    'label' => 'Описание',
                    'required' => false,
                    'help' => 'Описание услуги для онлайн бронирования'
                ])
                ->add('price', 'text', [
                    'label' => 'Цена',
                    'required' => false,
                    'attr' => ['placeholder' => 'Услуга не используется', 'class' => 'spinner price-spinner'],
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Service'
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_service_type';
    }

}
