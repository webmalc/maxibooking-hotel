<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class InvitedCityType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class InvitedCityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country', 'text', [
                'required' => false,
                'label' => 'Country',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('city', 'text', [
                'required' => false,
                'label' => 'City',
                'constraints' => [
                    new NotBlank()
                ],
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }

    public function getName()
    {
        return 'mbh_online_bundle_invite_city';
    }

}