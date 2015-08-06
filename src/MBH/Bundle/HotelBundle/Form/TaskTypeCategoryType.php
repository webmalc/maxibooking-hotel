<?php
/**
 * Created by PhpStorm.
 * User: mb
 * Date: 05.08.15
 * Time: 18:06
 */

namespace MBH\Bundle\HotelBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskTypeCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', [
                'label' => 'form.taskTypeCategory.title',
                'group' => 'form.taskTypeCategory.general_info',
                'required' => true,
            ])
            ->add('fullTitle', 'text', [
                'label' => 'form.taskTypeCategory.fullTitle',
                'group' => 'form.taskTypeCategory.general_info',
                'required' => true
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
            'data_class' => 'MBH\Bundle\HotelBundle\Document\TaskTypeCategory'
        ]);
    }


    public function getName()
    {
        return 'mbh_bundle_hotelbundle_task_type_category';
    }

}