<?php

namespace MBH\Bundle\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{

    private $isNew;
    private $roles;

    public function __construct($isNew = true, array $roles = [] )
    {
        $this->isNew = $isNew;
        $this->roles = [];
        
        foreach($roles as $key => $role) {
            $this->roles[$key] = $key;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('username', 'text', array('label' => 'Логин', 'group' => 'Данные аутентификации', 'attr' => array('placeholder' => 'ivan'),))
                ->add('email', 'email', array('label' => 'E-mail', 'group' => 'Данные аутентификации', 'attr' => ['placeholder' => 'ivan@example.com']))
        ;

        if ($this->isNew) {
            $builder->add('plainPassword', 'repeated', array(
                'group' => 'Данные аутентификации',
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password', 'attr' => array('autocomplete' => 'off'),),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
                'constraints' => new NotBlank()
            ));
        } else {
            $builder->add('newPassword', 'repeated', array(
                'group' => 'Данные аутентификации',
                'type' => 'password',
                'mapped' => false,
                'required' => false,
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'Новый пароль', 'attr' => array('autocomplete' => 'off'),),
                'second_options' => array('label' => 'Подтвердите пароль'),
                'invalid_message' => 'fos_user.password.mismatch',
                'constraints' => new Length(array('min' => 6))
            ));
        }

        $builder
                ->add('roles', 'choice', array(
                    'group' => 'Настройки',
                    'label' => 'Роли',
                    'multiple' => true,
                    'choices' => $this->roles,
                    'translation_domain' => 'MBHUserBundleRoles',
                    'attr' => array('class' => "chzn-select roles")
                ))
                ->add('enabled', 'checkbox', array(
                    'group' => 'Настройки',
                    'label' => 'Включен?',
                    'value' => true,
                    'required' => false,
                ))
                ->add('emailNotifications', 'checkbox', array(
                    'group' => 'Настройки',
                    'label' => 'Уведомления?',
                    'value' => true,
                    'required' => false,
                    'help' => 'Получать ли уведомления на электронную почту?'
                ))
                ->add('lastName', 'text', array('required' => false, 'label' => 'Фамилия', 'group' => 'Общая информация', 'attr' => ['placeholder' => 'Иванов']))
                ->add('firstName', 'text', array('required' => false, 'label' => 'Имя', 'group' => 'Общая информация', 'attr' => ['placeholder' => 'Иван']))

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\UserBundle\Document\User'
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_userbundle_usertype';
    }

}
