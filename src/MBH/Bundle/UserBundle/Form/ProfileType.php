<?php

namespace MBH\Bundle\UserBundle\Form;

use MBH\Bundle\BaseBundle\Form\LanguageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class ProfileType extends AbstractType
{
    private $defaultLocale;

    public function __construct($defaultLocale) {
        $this->defaultLocale = $defaultLocale;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'group' => 'form.profileType.enter_new_password',
                'options' => ['translation_domain' => 'FOSUserBundle'],
                'first_options' => ['label' => 'form.password', 'attr' => ['autocomplete' => 'off', 'class' => 'password']],
                'second_options' => ['label' => 'form.password_confirmation'],
                'invalid_message' => 'fos_user.password.mismatch',
                'required' => false,
                'constraints' => [
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
            ])
                ->add('locale', LanguageType::class, [
                    'label' => 'form.userType.locale',
                    'group' => 'form.userType.general_info',
                    'data' => $builder->getData() && $builder->getData()->getLocale()
                        ? $builder->getData()->getLocale()
                        : $this->defaultLocale
                ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\UserBundle\Document\User'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_userbundle_profiletype';
    }

}
