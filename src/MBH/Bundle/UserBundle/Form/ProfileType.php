<?php

namespace MBH\Bundle\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class ProfileType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'group' => 'Введите новый пароль',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password', 'attr' => array('autocomplete' => 'off', 'class' => 'password'),),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Пароль не может быть меньше 8 символов'
                    ]),
                    new Regex([
                        'pattern' => '/\d/',
                        'message' => 'Пароль должен содержать хотя бы одну цифру'
                    ]),
                    new Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Пароль должен содержать хотя бы одну заглавную букву'
                    ])
                ]
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\UserBundle\Document\User'
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_userbundle_profiletype';
    }

}
