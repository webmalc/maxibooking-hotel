<?php

namespace MBH\Bundle\HotelBundle\Form;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\TaskTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class DailyTaskType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class DailyTaskType extends AbstractType
{
    /**
     * @var Hotel
     */
    protected $hotel;

    public function __construct(Hotel $hotel)
    {
        $this->hotel = $hotel;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hotel = $this->hotel;
        $queryBuilderFunction = function(TaskTypeRepository $repository) use($hotel) {
            return $repository->createQueryBuilder()->field('hotel.id')->equals($hotel->getId());
        };

        $builder
            ->add('day', 'integer', [
                'required' => true,
                'attr' => [
                    'style' => 'width:60px',
                    'placeholder' => 'Дней',
                    'min' => 1,
                    'max' => 60
                ],
            ])
            ->add('taskType', 'document', [
                'required' => true,
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
                'group_by' => 'category',
                'attr' => [
                    'style' => 'width:250px',
                    'data-placeholder' => 'Выберите услугу'
                ],
                'empty_value' => '',
                'query_builder' => $queryBuilderFunction
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