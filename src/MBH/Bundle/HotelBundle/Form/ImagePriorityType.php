<?php


namespace MBH\Bundle\HotelBundle\Form;


use MBH\Bundle\BaseBundle\Form\Traits\ImagePriorityTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImagePriorityType extends AbstractType
{

    use ImagePriorityTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addPriorityType($builder, $options);
        $builder
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'views.hotel.roomType.editImages.edit.priority.save',
                    'attr' => [
                        'type' => 'button',
                        'class' => 'btn btn-primary'
                    ]
                ]
            );
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'data_class' => 'MBH\Bundle\BaseBundle\Document\Image',
                ]
            );
    }

    public function getBlockPrefix()
    {
        return 'image_priority_form';
    }


}