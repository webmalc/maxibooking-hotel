<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Form\Extension\DateTimeType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\UserBundle\Document\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaskType
 */
class TaskType extends AbstractType
{
    const SCENARIO_NEW = 'SCENARIO_NEW';
    const SCENARIO_EDIT = 'SCENARIO_EDIT';

    /**
     * @var DocumentManager
     */
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['scenario'] == self::SCENARIO_NEW) {
            $generalGroup = 'form.task.group.general_add';
        } elseif($options['scenario'] == self::SCENARIO_EDIT) {
            $generalGroup = 'form.task.group.general_edit';
        }

        $statuses = $options['statuses'];
        /** @var Hotel $hotel */
        $hotel = $options['hotel'];


        $queryBuilderSelectedHotelOnly = function(DocumentRepository $repository) use($hotel) {
            $queryBuilder = $repository->createQueryBuilder();
            $queryBuilder->field('hotel.id')->equals($hotel->getId());
            return $queryBuilder;
        };
        $builder
            ->add('type', DocumentType::class, [
                'label' => 'form.task.type',
                'group' => $generalGroup,
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
                'group_by' => 'category',
                'required' => true,
                'query_builder' => $queryBuilderSelectedHotelOnly
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'form.task.priority',
                'group' => $generalGroup,
                'choices' => $options['priorities'],
                'required' => true,
                'expanded' => true,
            ])
            ->add('date', DateTimeType::class, array(
                'label' => 'form.task.date',
                'html5' => false,
                'group' => $generalGroup,
                'required' => false,
                'time_widget' => 'single_text',
                'date_widget' => 'single_text',
                //'attr' => array('placeholder' => '12:00', 'class' => 'input-time'),
            ));
        if ($options['scenario'] == TaskType::SCENARIO_NEW) {
            $builder->add('housing', DocumentType::class, [
                'label' => 'form.task.housing',
                'group' => $generalGroup,
                'required' => false,
                'mapped' => false,
                //'attr' => ['class' => 'sm-input'],
                'class' => 'MBH\Bundle\HotelBundle\Document\Housing',
                'query_builder' => function (DocumentRepository $repository) use ($hotel) {
                    $queryBuilder = $repository->createQueryBuilder();
                    $queryBuilder->field('hotel.id')->equals($hotel->getId());
                    return $queryBuilder;
                }
            ]);
            $floors = $this->dm->getRepository('MBHHotelBundle:Room')->getFloorsByHotel($hotel);
            $builder->add('floor', ChoiceType::class, [
                'label' => 'form.task.floor',
                'group' => $generalGroup,
                'required' => false,
                'mapped' => false,
                'choices' => array_combine($floors, $floors)
            ]);

            $builder->add('rooms', DocumentType::class, [
                'label' => 'form.task.rooms',
                'group' => $generalGroup,
                'class' => 'MBH\Bundle\HotelBundle\Document\Room',
                'required' => true,
                'multiple' => true,
                'mapped' => false,
                'choice_attr' => function ($currentRoom) {
                    /** @var $currentRoom Room */
                    return [
                        'data-floor' => $currentRoom->getFloor(),
                        'data-housing' => $currentRoom->getHousing() ? $currentRoom->getHousing()->getId() : '',
                    ];
                },
            ]);
        } elseif ($options['scenario'] == TaskType::SCENARIO_EDIT) {
            $builder->add('room', DocumentType::class, [
                'label' => 'form.task.room',
                'group' => $generalGroup,
                'group_by' => 'roomType',
                'class' => 'MBH\Bundle\HotelBundle\Document\Room',
                'required' => true,
            ]);
        }
        $builder
            ->add('userGroup', DocumentType::class, [
                'label' => 'form.task.roles',
                'group' => 'form.task.group.assign',
                //'multiple' => true,
                'choice_translation_domain' => 'MBHUserBundleRoles',
                'attr' => array('class' => "chzn-select roles"),
                'required' => false,
                'class' => Group::class
            ])
            ->add('performer', DocumentType::class, [
                'label' => 'form.userType.users',
                'group' => 'form.task.group.assign',
                'class' => 'MBH\Bundle\UserBundle\Document\User',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'form.task.description',
                'group' => 'form.task.group.settings',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'form.task.status',
                'group' => 'form.task.group.settings',
                'required' => true,
                'choices' => $statuses,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Task',
            'priorities' => [],
            'statuses' => [],
            'scenario' => self::SCENARIO_NEW,
            'hotel' => null
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotelbundle_task';
    }

}