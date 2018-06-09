<?php

namespace MBH\Bundle\BaseBundle\Form;

use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FacilitiesType

 */
class FacilitiesType extends AbstractType
{
    use ContainerAwareTrait;

    public function getParent()
    {
        return  InvertChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->container->get('mbh.facility_repository')->getAllByGroupWithoutDescription(),
            'multiple' => true,
            'choice_attr' => function($key) {
                return [
                    'data-icon' => 'mbf-'.$key
                ];
            },
            'translation_domain' => 'messages',
            'attr' => [
                'class' => 'plain-html',
                'placeholder' => 'form.facilitiesType.placeholder'
            ],
            'placeholder' => '',
            'label' => 'form.facilitiesType.label',
            'by_reference' => false,
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_facilities';
    }
}