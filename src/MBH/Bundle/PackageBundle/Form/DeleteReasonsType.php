<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteReasonsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'form.deleteReasonsType.name',
                'group' => 'form.deleteReasonsType.add_source',
                'required' => true,
                'attr' => ['placeholder' => 'form.deleteReasonsType.adds']
            ])
            ->add('isDefault', CheckboxType::class, [
                'label' => 'form.deleteReasonsType.default',
                'group' => 'form.deleteReasonsType.add_source',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PackageBundle\Document\DeleteReason',
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_packagepackagesourecetype';
    }

}
