<?php

namespace MBH\Bundle\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'label' => 'form.groupType.name',
                'group' => 'form.groupType.group.main',
                'attr' => array('placeholder' => 'form.groupType.name.placeholder'),
            ])
            ->add('roles', 'roles', [
                'label' => 'form.userType.roles',
                'group' => 'form.groupType.group.roles',
                'multiple' => true,
                'translation_domain' => 'MBHUserBundleRoles'
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
        $resolver->setDefaults(['data_class' => 'MBH\Bundle\UserBundle\Document\Group']);
    }

    public function getName()
    {
        return 'mbh_bundle_userbundle_grouptype';
    }

}
