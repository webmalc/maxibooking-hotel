<?php

namespace MBH\Bundle\HotelBundle\Form;

use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use \MBH\Bundle\BaseBundle\Document\Image as BaseImage;
use Vich\UploaderBundle\Form\Type\VichImageType;

class HotelLogoImageType extends AbstractType
{
    private $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

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
        /** @var Hotel $hotel */
        $hotel = $options['hotel'];
        $logoImageDeleteUrl = $this->router->generate('hotel_delete_logo_image', ['id' => $hotel->getId()]);

        $logoDownloadUrl = null;
        if ($hotel->getLogoImage()) {
            $logoImageId = $hotel->getLogoImage()->getId();
            $logoDownloadUrl = $this->router->generate('hotel_logo_download', ['id' => $logoImageId]);
        }

        parent::finishView($view, $form, $options);
        $view->children['imageFile']->vars['logo_image_delete_url'] = $logoImageDeleteUrl;
        $view->children['imageFile']->vars['download_uri'] = $logoDownloadUrl;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BaseImage::class,
            'hotel' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'hotel_image_logo_form';
    }
}