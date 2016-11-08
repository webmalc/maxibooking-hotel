<?php

namespace MBH\Bundle\BaseBundle\Form;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FacilitiesType

 */
class FacilitiesType extends AbstractType
{
    use ContainerAwareTrait;

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->container->get('mbh.facility_repository')->getAllByGroup(),
            'multiple' => true,
            'choice_attr' => function($key, $label) {
                return [
                    'data-icon' => 'mbf-'.$key
                ];
            },
            'translation_domain' => 'messages',
            'attr' => [
                'class' => 'plain-html',
                'placeholder' => 'form.facilitiesType.placeholder'
            ],
            'empty_value' => '',
            'label' => 'form.facilitiesType.label',
            'by_reference' => false,
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_facilities';
    }
}