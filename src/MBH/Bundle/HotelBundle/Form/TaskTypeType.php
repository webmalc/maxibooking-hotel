<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\UserBundle\Document\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaskTypeType
 */
class TaskTypeType extends AbstractType
{
    const SCENARIO_NEW = 'new';
    const SCENARIO_EDIT = 'edit';

    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $group = $options['scenario'] == self::SCENARIO_NEW ?
            'form.taskType.general_info' :
            'form.taskType.general_info_edit';
        $builder
            ->add('title', 'text', [
                'label' => 'form.taskType.title',
                'group' => $group,
                'required' => true,
                'attr' => ['placeholder' => ''],
            ])
            ->add('category', 'hidden', [
                'required' => true
            ])
            ->add('defaultUserGroup', 'document', [
                'label' => 'form.taskType.default_user_group',
                'required' => true,
                'group' => $group,
                'class' => Group::class
            ])
            ->add('roomStatus', 'document', [
                'label' => 'form.taskType.roomStatus',
                'group' => $group,
                'required' => false,
                'class' => 'MBH\Bundle\HotelBundle\Document\RoomStatus',
                'empty_value' => '',
            ]);
        $builder->get('category')->addViewTransformer(new EntityToIdTransformer($this->dm,
            'MBH\Bundle\HotelBundle\Document\TaskTypeCategory'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
            'types' => [],
            'roles' => [],
            'scenario' => self::SCENARIO_NEW
        ));
    }


    public function getName()
    {
        return 'mbh_bundle_hotelbundle_tasktype';
    }

}