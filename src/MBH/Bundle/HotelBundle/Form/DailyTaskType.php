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
            ->add('day', 'integer', [
                'required' => true,
                'attr' => [
                    'style' => 'width:65px',
                    'placeholder' => 'Дней',
                    'min' => 1,
                    'max' => 60
                ],
                /*'constraints' => [
                    new Length(['max' => 3])
                ]*/
            ])
            ->add('taskType', 'document', [
                'required' => true,
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
                'group_by' => 'category',
                'attr' => [
                    'style' => 'width:395px',
                    'placeholder' => 'Выберите услугу'
                ],
                'empty_value' => ''
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\DailyTaskSetting',
        ]);
    }


    public function getName()
    {
        return 'mbh_bundle_hotel_daily_task';
    }
}