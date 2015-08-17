<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Class RoomTypeTasksType
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class RoomTypeTasksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('checkIn', 'document', [
                'label' => 'form.roomTypeTasks.checkIn',
                'required' => false,
                'multiple' => true,
                'group_by' => 'category',
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
                'help' => 'Задачи, создаваемые при заезде гостя'
            ])
            ->add('checkOut', 'document', [
                'label' => 'form.roomTypeTasks.checkOut',
                'required' => false,
                'multiple' => true,
                'group_by' => 'category',
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
                'help' => 'При выезде гостя'
            ])
            ->add('daily', 'collection', [
                'label' => 'form.roomTypeTasks.daily',
                'required' => false,
                'type' => new DailyTaskType(),
                'allow_add' => true,
                'allow_delete' => true,
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'cascade_validation' => true,
        ]);
    }


    public function getName()
    {
        return 'mbh_hotel_bundle_room_type_tasks';
    }
}