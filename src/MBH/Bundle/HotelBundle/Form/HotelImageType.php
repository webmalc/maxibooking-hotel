<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class HotelImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('imageFile', FileType::class, [
                'label' => 'form.hotel_images.image_file.label',
                'required' => false,
                'help' => 'form.hotel_images.image_file.help',
                'constraints' => [new Image(), new NotBlank()],
                'group' => $options['group_title']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => \MBH\Bundle\BaseBundle\Document\Image::class,
            'group_title' => 'form.hotel_images.groups.images',
            'buttonId' => 'upload-image-button'
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['buttonTitle'] = 'views.fields_group_with_button.button.title';
        $view->vars['buttonId'] = $options['buttonId'];
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_hotel_image_type';
    }
}
