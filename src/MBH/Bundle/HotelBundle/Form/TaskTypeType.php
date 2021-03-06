<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\UserBundle\Document\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->dm = $options['dm'];

        $group = $options['scenario'] == self::SCENARIO_NEW ?
            'form.taskType.general_info' :
            'form.taskType.general_info_edit';
        $builder
            ->add('title', TextType::class, [
                'label' => 'form.taskType.title',
                'group' => $group,
                'required' => true,
                'attr' => ['placeholder' => ''],
            ])
            ->add('category', HiddenType::class, [
                'required' => true
            ])
            ->add('defaultUserGroup', DocumentType::class, [
                'label' => 'form.taskType.default_user_group',
                'required' => true,
                'group' => $group,
                'class' => Group::class
            ])
            ->add('roomStatus', DocumentType::class, [
                'label' => 'form.taskType.roomStatus',
                'group' => $group,
                'required' => false,
                'class' => 'MBH\Bundle\HotelBundle\Document\RoomStatus',
                'placeholder' => '',
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
            'scenario' => self::SCENARIO_NEW,
            'dm' => null
        ));
    }


    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotelbundle_tasktype';
    }

}