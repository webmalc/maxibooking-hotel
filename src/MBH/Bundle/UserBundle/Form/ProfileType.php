<?php

namespace MBH\Bundle\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ProfileType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'group' => 'form.profileType.enter_new_password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password', 'attr' => array('autocomplete' => 'off', 'class' => 'password'),),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'form.profileType.min_password'
                    ]),
                    new Regex([
                        'pattern' => '/\d/',
                        'message' => 'form.profileType.min_number_password'
                    ]),
                    new Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'form.profileType.min_capital_letter_password'
                    ])
                ]
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\UserBundle\Document\User'
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_userbundle_profiletype';
    }

}
