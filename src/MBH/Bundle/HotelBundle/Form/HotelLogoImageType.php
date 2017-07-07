<?php


namespace MBH\Bundle\HotelBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use \MBH\Bundle\BaseBundle\Document\Image as BaseImage;


class HotelLogoImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('imageFile', FileType::class, [
                'label' => false,
                'required' => false,
                'help' => 'form.hotel_images.image_file.help',
                'constraints' => [new Image(), new NotBlank()],
                'group' => 'no-group'

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BaseImage::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'hotel_image_logo_form';
    }


}