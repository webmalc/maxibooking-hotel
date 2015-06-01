<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\UserBundle\Form\UserType;

class Task extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder
            ->add('taskType', 'text', [
                'label' => 'form.task.taskType',
                'group' => 'form.task.general_info',
                'required' => true,
                'attr' => ['placeholder' => 'form.task.attr_taskType']
            ]);

        $builder
            ->add('room', 'text', [
                'label' => 'form.task.room',
                'group' => 'form.task.general_room',
                'required' => true,
                'attr' => ['placeholder' => 'form.task.attr_room']
            ]);


        $builder->add('roomType', 'document', array(
                'group' => 'form.task.roomType',
                'label' => 'form.task.roomType',
                'multiple' => true,
                'mapped' => true,
                'data' => array(),
                'class' => 'MBHHotelBundle:RoomType',
                'property' => 'name',
                 'attr' => array('class' => "chzn-select")
            ));


        /*$builder
            ->add('roles', 'choice', array(
                'group' => 'form.userType.settings',
                'label' => 'form.userType.roles',
                'multiple' => true,
                'choices' => $this->roles,
                'translation_domain' => 'MBHUserBundleRoles',
                'attr' => array('class' => "chzn-select roles")
            )); */



        $builder
                ->add('creationalDate', 'date', array(
                'label' => 'form.task.creationalDate',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'data' => new \DateTime(),
                'required' => true,
                'error_bubbling' => true,
                'attr' => array('class' => 'datepicker begin-datepiker', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(['message' => 'form.searchType.check_in_date_not_filled']), new Date()]
            ));


        $builder
                ->add('updatableDate', 'date', array(
                'label' => 'form.task.updatableDate',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'data' => new \DateTime(),
                'required' => true,
                'error_bubbling' => true,
                    'attr' => array('class' => 'datepicker begin-datepiker', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(['message' => 'form.searchType.check_in_date_not_filled']), new Date()]
                ));

        $builder
                ->add('description', 'textarea', [
                    'label' => 'form.task.description',
                    'group' => '',
                    'required' => true,
                    'attr' => ['placeholder' => 'form.task.attr_description']
                ]);




    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Task',
            'types' => [],
            'roles' => []

        ));
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_task';
    }

}