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
                ->add('username', 'text', array('label' => 'form.userType.login', 'group' => 'form.userType.authentication_data', 'attr' => array('placeholder' => 'ivan'),))
                ->add('email', 'email', array('label' => 'E-mail', 'group' => 'form.userType.authentication_data', 'attr' => ['placeholder' => 'ivan@example.com']))
        ;

        if ($this->isNew) {
            $builder->add('plainPassword', 'repeated', array(
                'group' => 'form.userType.authentication_data',
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password', 'attr' => array('autocomplete' => 'off', 'class' => 'password'),),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
                'constraints' => new NotBlank()
            ));
        } else {
            $builder->add('newPassword', 'repeated', array(
                'group' => 'form.userType.authentication_data',
                'type' => 'password',
                'mapped' => false,
                'required' => false,
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.userType.new_password', 'attr' => array('autocomplete' => 'off', 'class' => 'password'),),
                'second_options' => array('label' => 'form.userType.confirm_password'),
                'invalid_message' => 'fos_user.password.mismatch',
                'constraints' => new Length(array('min' => 6))
            ));
        }

        $builder
                ->add('roles', 'choice', array(
                    'group' => 'form.userType.settings',
                    'label' => 'form.userType.roles',
                    'multiple' => true,
                    'choices' => $this->roles,
                    'translation_domain' => 'MBHUserBundleRoles',
                    'attr' => array('class' => "chzn-select roles")
                ));

        if(!$options['admin']) {

            $builder->add('hotels', 'document', array(
                    'group' => 'form.userType.settings',
                    'label' => 'form.userType.hotels',
                    'multiple' => true,
                    'mapped' => false,
                    'data' => $options['hotels'],
                    'class' => 'MBHHotelBundle:Hotel',
                    'property' => 'name',
                    'help' => 'form.userType.hotels_user_has_access_to',
                    'attr' => array('class' => "chzn-select")
                ));
        }

        $builder
                ->add('enabled', 'checkbox', array(
                    'group' => 'form.userType.settings',
                    'label' => 'form.userType.is_included',
                    'value' => true,
                    'required' => false,
                ))
                ->add('emailNotifications', 'checkbox', array(
                    'group' => 'form.userType.settings',
                    'label' => 'form.userType.notifications',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.userType.is_email_notifications'
                ))
                ->add('lastName', 'text', array('required' => false, 'label' => 'form.userType.surname', 'group' => 'form.userType.general_info', 'attr' => ['placeholder' => 'form.userType.placeholder_surname']))
                ->add('firstName', 'text', array('required' => false, 'label' => 'form.userType.name', 'group' => 'form.userType.general_info', 'attr' => ['placeholder' => 'form.userType.placeholder_name']))

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\UserBundle\Document\User',
            'admin' => false,
            'hotels' => []
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_userbundle_usertype';
    }

}
