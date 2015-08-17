<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


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
                'mapped' => false,
                'multiple' => true,
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
            ])
            ->add('checkOut', 'document', [
                'label' => 'form.roomTypeTasks.checkOut',
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
            ])
            ->add('daily', 'collection', [
                'label' => 'form.roomTypeTasks.daily',
                'required' => false,
                'mapped' => false,
                'type' => new DailyTaskType(),
                'allow_add' => true,
            ]);
    }

    public function getName()
    {
        return 'mbh_hotel_bundle_room_type_tasks';
    }
}