<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Class TaskType
 */
class TaskType extends AbstractType
{
    const SCENARIO_NEW = 'SCENARIO_NEW';
    const SCENARIO_EDIT = 'SCENARIO_EDIT';

    protected $dm;

    public function __construct($dm)
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

        $roles = [];
        foreach ($options['roles'] as $key => $role) {
            $roles[$key] = $key;
        }

        $statuses = $options['statuses'];

        $builder
            ->add('type', 'choice', [
                'label' => 'form.task.type',
                'group' => $generalGroup,
                'choices' => $options['taskTypes'],
                'required' => true
            ])
            ->add('priority', 'choice', [
                'label' => 'form.task.priority',
                'group' => $generalGroup,
                'choices' => $options['priorities'],
                'required' => true,
                'expanded' => true,
            ])
            /*->add('date', 'date', [
                'label' => 'form.task.date',
                'group' => $generalGroup,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['class' => 'input-small'],
                //'required' => true,
                //'data' => new \DateTime()
            ])*/
            ->add('date', 'datetime', array(
                'label' => 'form.task.date',
                'html5' => false,
                'group' => $generalGroup,
                'required' => false,
                'time_widget' => 'single_text',
                'date_widget' => 'single_text',
                //'attr' => array('placeholder' => '12:00', 'class' => 'input-time'),
            ))
            ->add('room', 'document', [
                'label' => 'form.task.room',
                'group' => $generalGroup,
                'choices' => $options['optGroupRooms'],
                'class' => 'MBH\Bundle\HotelBundle\Document\Room',
                'required' => true,
            ])
            ->add('role', 'choice', [
                'label' => 'form.task.roles',
                'group' => 'form.task.group.assign',
                //'multiple' => true,
                'choices' => $roles,
                'choice_translation_domain' => 'MBHUserBundleRoles',
                'attr' => array('class' => "chzn-select roles"),
                'required' => false
            ])
            ->add('performer', 'document', [
                'label' => 'form.userType.users',
                'group' => 'form.task.group.assign',
                'class' => 'MBH\Bundle\UserBundle\Document\User',
                'required' => false
            ])
            ->add('description', 'textarea', [
                'label' => 'form.task.description',
                'group' => 'form.task.group.settings',
                'required' => false,
            ])
            ->add('status', 'choice', [
                'label' => 'form.task.status',
                'group' => 'form.task.group.settings',
                'required' => true,
                'choices' => $statuses,
                'expanded' => true,
            ])
        ;

        $builder->get('type')->addModelTransformer(new EntityToIdTransformer($this->dm, 'MBH\Bundle\HotelBundle\Document\TaskType'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Task',
            'roles' => [],
            'taskTypes' => [],
            'priorities' => [],
            'optGroupRooms' => [],
            'statuses' => [],
            'scenario' => self::SCENARIO_NEW
        ));
    }


    public function getName()
    {
        return 'mbh_bundle_hotelbundle_task';
    }

}