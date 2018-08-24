<?php


namespace MBH\Bundle\BaseBundle\Form;


use MBH\Bundle\BaseBundle\Document\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image as ConstraintsImage;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isDefault', CheckboxType::class, [
                'required' => false,
                'value' => false,
                'label' => 'form.image.is_default.label',
                'help' => 'form.image.is_default.help',
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'form.image.file.label',
                'required' => false,
                'help' => 'form.image.file.help',
                'constraints' => $options['hasConstraints'] ? [new ConstraintsImage(), new NotBlank()] : []
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
            'hasConstraints' => true
        ]);
    }

}