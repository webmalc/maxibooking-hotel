<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class InvitedTouristType
 * @package MBH\Bundle\OnlineBundle\Form

 */
class InvitedTouristType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.imya',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('lastName', 'text', [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.familiya',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('sex', 'choice', [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.obrashcheniye',
                'expanded' => true,
                'choices' => [
                    'Господин',
                    'Госпожа'
                ],
                'empty_value' => null,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('birthday', 'text', [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.datarozhdeniya',
                //'widget' => 'single_text',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('birthplace', 'text', [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.mestorozhdeniya',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('citizenship', 'text', [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.grazhdanstvo',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('passport', 'text', [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.pasport',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('expiry', 'text', [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.deystvuyetdo',
                //'widget' => 'single_text',
                //'format' => 'yyyy-MM-dd',
                'constraints' => [
                    new NotBlank()
                ],
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\OnlineBundle\Document\InvitedTourist'
        ]);
    }

    public function getName()
    {
        return 'mbh_online_bundle_invited_tourist';
    }

}
