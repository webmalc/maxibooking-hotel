<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;

class Task extends AbstractType
{
    const SCENARIO_NEW = 'SCENARIO_NEW';
    const SCENARIO_EDIT = 'SCENARIO_EDIT';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['scenario'] == self::SCENARIO_NEW)
            $group = 'form.task.general_group_add';
        elseif($options['scenario'] == self::SCENARIO_EDIT)
            $group = 'form.task.general_group_edit';

        $builder
            ->add('taskType', 'document', [
                'class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
                'label' => 'form.task.taskType',
                'group' => $group,
                'required' => true,
                //'attr' => ['placeholder' => 'form.task.attr_taskType']
            ]);

        $builder
            /*->add('room', 'text', [
                'label' => 'form.task.room',
                'group' => 'form.task.general_room',
                'required' => true,
            ]);*/
            ->add('room', 'document', [
                'label' => 'form.task.room',
                'group' => $group,
                'class' => 'MBH\Bundle\HotelBundle\Document\Room',
                'required' => true,
            ]);

        $builder
            ->add('priority', 'choice', [
                'label' => 'form.task.priority',
                'group' => $group,
                'choices' => $options['priorities'],
                'required' => true,
            ]);

        $builder
            ->add('guest', 'document', [
                'label' => 'form.task.tourist',
                'group' => $group,
                'class' => 'MBH\Bundle\PackageBundle\Document\Tourist',
                'required' => true,
            ]);

        $builder
            ->add('role', 'choice', array(
                'group' => $group,
                'label' => 'form.userType.roles',
                //'multiple' => true,
                'choices' => $options['roles'],
                'translation_domain' => 'MBHUserBundleRoles',
                'attr' => array('class' => "chzn-select roles"),
                'required' => false
            ));

        $builder
            ->add('date', 'date', [
                'widget' => 'single_text',
                'label' => 'form.task.date',
                'format' => 'dd.MM.yyyy',
                'attr' => ['class' => 'input-small'],
                //'required' => true,
                //'data' => new \DateTime()
            ]);

        $builder
            ->add('description', 'textarea', [
                'label' => 'form.task.description',
                'group' => $group,
                'required' => false,
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Task',
            'roles' => [],
            'priorities' => [],
            'scenario' => self::SCENARIO_NEW
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_task';
    }

}