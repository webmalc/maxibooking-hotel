<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceCategoryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', TextType::class, [
                    'label' => 'Название',
                    'required' => true,
                    'attr' => ['placeholder' => 'Основные услуги']
                ])
                ->add('title', TextType::class, [
                    'label' => 'Внутреннее название',
                    'required' => false,
                    'attr' => ['placeholder' => 'Основные услуги - лето ' . date('Y')],
                    'help' => 'Название для использования внутри MaxiBooking'
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Описание',
                    'required' => false,
                    'help' => 'Описание категории услуг для онлайн бронирования'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\ServiceCategory'
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_service_category_type';
    }

}
