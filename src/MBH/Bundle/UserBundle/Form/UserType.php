<?php

namespace MBH\Bundle\UserBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    private $isNew;
    private $roles;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->isNew = $options['isNew'];
        $this->roles = [];

        foreach ($options['roles'] as $key => $role) {
            $this->roles[$key] = $key;
        }

        $builder
            ->add('username', TextType::class, [
                'label' => 'form.userType.login',
                'group' => 'form.userType.authentication_data',
                'attr' => array('placeholder' => 'ivan'),
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'group' => 'form.userType.authentication_data',
                'attr' => ['placeholder' => 'ivan@example.com']
            ]);

        if ($this->isNew) {
            $builder->add('plainPassword', RepeatedType::class, [
                'group' => 'form.userType.authentication_data',
                'type' => PasswordType::class,
                'first_options' => array(
                    'label' => 'form.password',
                    'attr' => array('autocomplete' => 'off', 'class' => 'password'),
                ),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
                'constraints' => new NotBlank()
            ]);
        } else {
            $builder->add('newPassword', RepeatedType::class, [
                'group' => 'form.userType.authentication_data',
                'type' => PasswordType::class,
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
            ->add('hotels', DocumentType::class, [
                'group' => 'form.userType.settings',
                'label' => 'form.userType.hotels',
                'multiple' => true,
                'mapped' => true,
                'required' => false,
                'class' => Hotel::class,
                'choice_label' => 'name',
                'help' => 'form.userType.hotels_user_has_access_to',
                'attr' => array('class' => "chzn-select")
            ])
            ->add('isEnabledWorkShift', CheckboxType::class, [
                'label' => 'form.clientConfigType.is_enabled_work_shift',
                'group' => 'form.userType.settings',
                'required' => false,
            ])
            ->add('defaultNoticeDoc', CheckboxType::class, [
                'label' => 'form.clientConfigType.default_notice_doc',
                'help' => 'form.clientConfigType.default_notice_doc_desc',
                'group' => 'form.userType.settings',
                'required' => false,
            ])
        ;

        $builder
            ->add('notifications', CheckboxType::class, [
                'group' => 'form.userType.notifications_fieldset',
                'label' => 'form.userType.notifications',
                'value' => true,
                'required' => false,
            ])
            ->add('taskNotify', CheckboxType::class, [
                'group' => 'form.userType.notifications_fieldset',
                'label' => 'form.userType.taskNotify',
                'value' => true,
                'required' => false,
            ])
            ->add('reports', CheckboxType::class, [
                'group' => 'form.userType.notifications_fieldset',
                'label' => 'form.userType.reports',
                'value' => true,
                'required' => false,
            ])
            ->add('errors', CheckboxType::class, [
                'group' => 'form.userType.notifications_fieldset',
                'label' => 'form.userType.errors',
                'value' => true,
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
                'label' => 'form.userType.surname',
                'group' => 'form.userType.general_info',
                'attr' => ['placeholder' => 'form.userType.placeholder_surname']
            ])
            ->add('firstName', TextType::class,[
                'required' => false,
                'label' => 'form.userType.name',
                'group' => 'form.userType.general_info',
                'attr' => ['placeholder' => 'form.userType.placeholder_name']
            ])
            ->add('patronymic', TextType::class,[
                'required' => false,
                'label' => 'form.userType.patronymic',
                'group' => 'form.userType.general_info',
                'attr' => ['placeholder' => 'form.userType.placeholder_patronymic']
            ])
            ->add('birthday', DateType::class, array(
                'label' => 'form.userType.birth_date',
                'group' => 'form.userType.general_info',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker-year'),
            ))
        ;

        $myExtraFieldValidator = function(FormEvent $event){
            $form = $event->getForm();
            $hotelsFiled = $form->get('hotels');
            if ($form->get('isEnabledWorkShift')->getData() && count($hotelsFiled->getData()) != 1) {
                $hotelsFiled->addError(new FormError('Для включения рабочих смен должен быть выбран один отель'));
            }
        };

        // adding the validator to the FormBuilderInterface
        $builder->addEventListener(FormEvents::POST_SUBMIT, $myExtraFieldValidator);
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
            'roles' => [],
            'isNew' => true
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_userbundle_usertype';
    }

}
