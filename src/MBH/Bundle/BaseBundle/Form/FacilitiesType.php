<?php

namespace MBH\Bundle\BaseBundle\Form;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FacilitiesType
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class FacilitiesType extends AbstractType
{
    use ContainerAwareTrait;

    public function getParent()
    {
        return 'choice';
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


    public function getName()
    {
        return 'mbh_facilities';
    }
}