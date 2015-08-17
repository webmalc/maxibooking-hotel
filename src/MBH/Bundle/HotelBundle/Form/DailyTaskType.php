<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class DailyTaskType
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class DailyTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('day', 'number', [
                'attr' => [
                    'style' => 'width:50px',
                    'placeholder' => 'Кол. дней',
                    //'max-length' => 3
                ],
                /*'constraints' => [
                    new Length(['max' => 3])
                ]*/
            ])
            ->add('task_type', 'document', [
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
                'attr' => ['style' => 'width:410px'],
                'empty_value' => ''
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'MBH\Bundle\HotelBundle\Document\Task',
        ));
    }


    public function getName()
    {
        return 'mbh_bundle_hotel_daily_task';
    }
}