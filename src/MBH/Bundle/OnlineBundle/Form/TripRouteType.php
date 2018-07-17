<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TripRouteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hotel', TextType::class, [
                'required' => false,
                'label' => 'mbhonlinebundle.form.triproutetype.gostinitsa',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('address', TextType::class, [
                'required' => false,
                'label' => 'mbhonlinebundle.form.triproutetype.gorod',
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

    public function getBlockPrefix()
    {
        return 'mbh_online_bundle_invite_trip_route';
    }

}