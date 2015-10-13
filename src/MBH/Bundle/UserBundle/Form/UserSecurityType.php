<?php

namespace MBH\Bundle\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSecurityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', 'checkbox', [
                'group' => 'form.userType.settings',
                'label' => 'form.userType.is_included',
                'value' => true,
                'required' => false,
            ])
            ->add('expiresAt', 'date', [
                'label' => 'form.userType.expires_at',
                'group' => 'form.userType.settings',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => ['data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker-year'],
                'help' => 'Если не указано - срок действия пользователя неограничен'
            ])
            ->add('groups', 'document', [
                'class' => 'MBHUserBundle:Group',
                'group' => 'form.groupType.group.roles',
                'label' => 'form.userType.groups',
                'required' => false,
                'multiple' => true
            ])
            ->add('rolesWithoutGroups', 'roles', [
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

    public function getName()
    {
        return 'mbh_bundle_userbundle_usersecuritytype';
    }

}
