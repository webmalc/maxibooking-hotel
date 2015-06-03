<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', [
                'label' => 'form.taskType.title',
                'group' => 'form.taskType.general_info',
                'required' => true,
                'attr' => ['placeholder' => ''],
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\TaskType',
            'types' => [],
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_tasktype';
    }

}
