<?php

namespace MBH\Bundle\UserBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Form\Type\RolesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSecurityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'group' => 'form.userType.settings',
                'label' => 'form.userType.is_included',
                'value' => true,
                'required' => false,
            ])
            ->add('twoFactorAuthentication',  InvertChoiceType::class, [
                'group' => 'form.userType.settings',
                'label' => 'form.userType.two_factor',
                'choices' => array_combine(User::getTwoFactorTypes(), User::getTwoFactorTypes()),
                'required' => false,
            ])
            ->add('expiresAt', DateType::class, [
                'label' => 'form.userType.expires_at',
                'group' => 'form.userType.settings',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => ['data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker-year'],
                'help' => 'form.userType.expires_at.help'
            ])
            ->add('groups', DocumentType::class, [
                'class' => 'MBHUserBundle:Group',
                'group' => 'form.groupType.group.roles',
                'label' => 'form.userType.groups',
                'required' => false,
                'multiple' => true
            ])
            ->add('rolesWithoutGroups', RolesType::class, [
                'group' => 'form.userType.security_extended',
                'label' => 'form.userType.roles',
                'multiple' => true,
                'required' => false,
                'translation_domain' => 'MBHUserBundleRoles',
                'attr' => array('class' => "roles")
            ])
        ;
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
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_userbundle_usersecuritytype';
    }

}
