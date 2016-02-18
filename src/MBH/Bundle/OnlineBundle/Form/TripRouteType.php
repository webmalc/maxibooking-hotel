<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TripRouteType

 */
class TripRouteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hotel', 'text', [
                'required' => false,
                'label' => 'Гостиница',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('address', 'text', [
                'required' => false,
                'label' => 'Город',
                'constraints' => [
                    new NotBlank()
                ],
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\OnlineBundle\Document\TripRoute'
        ]);
    }

    public function getName()
    {
        return 'mbh_online_bundle_invite_trip_route';
    }

}