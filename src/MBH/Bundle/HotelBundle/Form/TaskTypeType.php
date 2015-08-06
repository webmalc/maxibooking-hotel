<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaskTypeType
 */
class TaskTypeType extends AbstractType
{
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', [
                'label' => 'form.taskType.title',
                'group' => 'form.taskType.general_info',
                'required' => true,
                'attr' => ['placeholder' => ''],
            ])->add('category', 'hidden', [
                'required' => true
            ])
        ;
        $builder->get('category')->addViewTransformer(new EntityToIdTransformer($this->dm, 'MBH\Bundle\HotelBundle\Document\TaskTypeCategory'));
    }

    public function configureOptions(OptionsResolver $resolver)
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