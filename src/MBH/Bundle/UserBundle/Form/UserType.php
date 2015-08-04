<?php

namespace MBH\Bundle\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    private $isNew;
    private $roles;

    public function __construct($isNew = true, array $roles = [])
    {
        $this->isNew = $isNew;
        $this->roles = [];

        foreach ($roles as $key => $role) {
            $this->roles[$key] = $key;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', [
                'label' => 'form.userType.login',
                'group' => 'form.userType.authentication_data',
                'attr' => array('placeholder' => 'ivan'),
            ])
            ->add('email', 'email', [
                'label' => 'E-mail',
                'group' => 'form.userType.authentication_data',
                'attr' => ['placeholder' => 'ivan@example.com']
            ]);

        if ($this->isNew) {
            $builder->add('plainPassword', 'repeated', [
                'group' => 'form.userType.authentication_data',
                'type' => 'password',
                'first_options' => array(
                    'label' => 'form.password',
                    'attr' => array('autocomplete' => 'off', 'class' => 'password'),
                ),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
                'constraints' => new NotBlank()
            ]);
        } else {
            $builder->add('newPassword', 'repeated', [
                'group' => 'form.userType.authentication_data',
                'type' => 'password',
                'mapped' => false,
                'required' => false,
                'first_options' => array(
                    'label' => 'form.userType.new_password',
                    'attr' => array('autocomplete' => 'off', 'class' => 'password'),
                ),
                'second_options' => array('label' => 'form.userType.confirm_password'),
                'invalid_message' => 'fos_user.password.mismatch',
                'constraints' => new Length(array('min' => 6))
            ]);
        }

        $builder
            ->add('roles', 'choice', [
                'group' => 'form.userType.settings',
                'label' => 'form.userType.roles',
                'multiple' => true,
                'choices' => $this->roles,
                'translation_domain' => 'MBHUserBundleRoles',
                'attr' => array('class' => "chzn-select roles")
            ]);

        if (!$options['admin']) {
            $builder->add('hotels', 'document', [
                'group' => 'form.userType.settings',
                'label' => 'form.userType.hotels',
                'multiple' => true,
                'mapped' => false,
                'data' => $options['hotels'],
                'class' => 'MBHHotelBundle:Hotel',
                'property' => 'name',
                'help' => 'form.userType.hotels_user_has_access_to',
                'attr' => array('class' => "chzn-select")
            ]);
        }

        $builder
            ->add('enabled', 'checkbox', [
                'group' => 'form.userType.settings',
                'label' => 'form.userType.is_included',
                'value' => true,
                'required' => false,
            ])
            ->add('notifications', 'checkbox', [
                'group' => 'form.userType.notifications_fieldset',
                'label' => 'form.userType.notifications',
                'value' => true,
                'required' => false,
            ])
            ->add('reports', 'checkbox', [
                'group' => 'form.userType.notifications_fieldset',
                'label' => 'form.userType.reports',
                'value' => true,
                'required' => false,
            ])
            ->add('errors', 'checkbox', [
                'group' => 'form.userType.notifications_fieldset',
                'label' => 'form.userType.errors',
                'value' => true,
                'required' => false,
            ])
            ->add('lastName', 'text', [
                'required' => false,
                'label' => 'form.userType.surname',
                'group' => 'form.userType.general_info',
                'attr' => ['placeholder' => 'form.userType.placeholder_surname']
            ])
            ->add('firstName', 'text',[
                'required' => false,
                'label' => 'form.userType.name',
                'group' => 'form.userType.general_info',
                'attr' => ['placeholder' => 'form.userType.placeholder_name']
            ])
            ->add('patronymic', 'text',[
                'required' => false,
                'label' => 'form.userType.patronymic',
                'group' => 'form.userType.general_info',
                'attr' => ['placeholder' => 'form.userType.placeholder_patronymic']
            ])
            ->add('birthday', 'date', array(
                'label' => 'form.userType.birth_date',
                'group' => 'form.userType.general_info',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'),
            ));
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\UserBundle\Document\User',
            'admin' => false,
            'hotels' => []
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_userbundle_usertype';
    }

}
