<?php

namespace MBH\Bundle\UserBundle\Form;

use MBH\Bundle\UserBundle\Form\Type\RolesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.groupType.name',
                'group' => 'form.groupType.group.main',
                'attr' => array('placeholder' => 'form.groupType.name.placeholder'),
            ])
            ->add('roles', RolesType::class, [
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

    public function getBlockPrefix()
    {
        return 'mbh_bundle_userbundle_grouptype';
    }

}
