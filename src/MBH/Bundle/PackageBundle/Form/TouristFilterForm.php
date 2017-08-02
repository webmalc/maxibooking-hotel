<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\PackageBundle\Document\Criteria\TouristQueryCriteria;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TouristFilterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false
            ])
            ->add('end', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false
            ])
            ->add('citizenship', InvertChoiceType::class, [
                'required' => false,
                'choices' => [
                    TouristQueryCriteria::CITIZENSHIP_NATIVE => 'Граждане РФ',
                    TouristQueryCriteria::CITIZENSHIP_FOREIGN => 'Иностранные граждане'
                ]
            ])
            ->add('search', TextType::class, [
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TouristQueryCriteria::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhpackage_bundle_tourist_filter_form';
    }
}
