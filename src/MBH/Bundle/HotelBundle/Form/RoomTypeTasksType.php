<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\TaskTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Class RoomTypeTasksType

 */
class RoomTypeTasksType extends AbstractType
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
            ->add('checkIn', DocumentType::class, [
                'label' => 'form.roomTypeTasks.checkIn',
                'required' => false,
                'multiple' => true,
                'group_by' => 'category',
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
                'help' => 'Задачи, создаваемые при заезде гостя',
                'query_builder' => $queryBuilderFunction
            ])
            ->add('daily', CollectionType::class, [
                'label' => 'form.roomTypeTasks.daily',
                'required' => false,
                'type' => new DailyTaskType($hotel),
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('checkOut', DocumentType::class, [
                'label' => 'form.roomTypeTasks.checkOut',
                'required' => false,
                'multiple' => true,
                'group_by' => 'category',
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
                'help' => 'При выезде гостя',
                'query_builder' => $queryBuilderFunction
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


    public function getBlockPrefix()
    {
        return 'mbh_hotel_bundle_room_type_tasks';
    }
}