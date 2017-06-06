<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('firstName', TextType::class, [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.imya',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.familiya',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('sex',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.obrashcheniye',
                'expanded' => true,
                'choices' => [
                    'Господин',
                    'Госпожа'
                ],
                'placeholder' => null,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('birthday', TextType::class, [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.data.rozhdeniya',
                //'widget' => 'single_text',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('birthplace', TextType::class, [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.mesto.rozhdeniya',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('citizenship', TextType::class, [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.grazhdanstvo',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('passport', TextType::class, [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.pasport',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('expiry', TextType::class, [
                'required' => false,
                'label' => 'mbhonlinebundle.form.invitedtouristtype.deystvuyet.do',
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

    public function getBlockPrefix()
    {
        return 'mbh_online_bundle_invited_tourist';
    }

}
