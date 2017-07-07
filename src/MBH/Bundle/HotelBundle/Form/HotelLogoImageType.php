<?php


namespace MBH\Bundle\HotelBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use \MBH\Bundle\BaseBundle\Document\Image as BaseImage;
use Vich\UploaderBundle\Form\Type\VichImageType;


class HotelLogoImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('imageFile', FileType::class, [
            ->add('imageFile', VichImageType::class, [
                'download_uri' => false,
                'label' => false,
                'required' => false,
                'help' => 'form.hotel_logo.image_file.help',
                'group' => 'form.hotel_logo.group'
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