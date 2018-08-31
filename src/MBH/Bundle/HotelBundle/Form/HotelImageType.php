<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class HotelImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['withIsDefaultField']) {
            $builder
                ->add('isDefault', CheckboxType::class, [
                    'required' => false,
                    'value' => false,
                    'label' => 'form.hotel_images.image_is_default.label',
                    'help' => 'form.hotel_images.image_is_default.help'
                ]);

        }
        $builder
            ->add('imageFile', FileType::class, [
                'label' => 'form.hotel_images.image_file.label',
                'required' => false,
                'help' => 'form.hotel_images.image_file.help',
                'constraints' => $options['hasConstraints'] ? [new Image(), new NotBlank()] : []
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => \MBH\Bundle\BaseBundle\Document\Image::class,
            'withIsDefaultField' => true,
            'hasConstraints' => true
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_hotel_image_type';
    }
}
