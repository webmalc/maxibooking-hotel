<?php

namespace MBH\Bundle\HotelBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaskTypeCategoryType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class TaskTypeCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', 'text', [
                'label' => 'form.taskTypeCategory.fullTitle',
                'group' => 'form.taskTypeCategory.general_info',
                'required' => true,
            ])
            ->add('title', 'text', [
                'label' => 'form.taskTypeCategory.title',
                'group' => 'form.taskTypeCategory.general_info',
                'required' => false,
                'help' => 'form.hotelType.maxibooking_inner_name'
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
        return 'mbh_hotel_bundle_task_type_category';
    }

}