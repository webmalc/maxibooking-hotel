<?php


namespace  MBH\Bundle\PackageBundle\Form;


use MBH\Bundle\PackageBundle\Document\DeleteReasonCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteReasonCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('full_title', TextType::class, [
                'label' => 'package.delete.reason.category.form.fullTitle.label',
                'required' => true,
                'attr' => ['placeholder' => 'package.delete.reason.category.form.fullTitle.placeholder'],
                'help' => 'package.delete.reason.category.form.fullTitle.help'
            ])
            ->add('title', TextType::class, [
                'label' => 'package.delete.reason.category.form.title.label',
                'required' => false,
                'help' => 'package.delete.reason.category.form.title.help'
            ]);
    }

    public function getBlockPrefix()
    {
        return 'delete_reason_category_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => DeleteReasonCategory::class
            ]);
    }

}