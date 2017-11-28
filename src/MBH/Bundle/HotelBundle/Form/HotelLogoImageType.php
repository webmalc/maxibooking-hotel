<?php


namespace MBH\Bundle\HotelBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use \MBH\Bundle\BaseBundle\Document\Image as BaseImage;
use Vich\UploaderBundle\Form\Type\VichImageType;


class HotelLogoImageType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'allow_delete' => false,
                'label' => false,
                'required' => false,
                'help' => 'form.hotel_logo.image_file.help',
                'group' => 'no-group'
            ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        $view->children['imageFile']->vars['logo_image_delete_url'] = $options['logo_image_delete_url'];
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BaseImage::class,
            'logo_image_delete_url' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'hotel_image_logo_form';
    }


}