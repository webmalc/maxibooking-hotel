<?php

namespace MBH\Bundle\HotelBundle\Form\HotelFlow;

use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Service\MBHFormBuilder;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\ContactInfoType;
use MBH\Bundle\HotelBundle\Form\LogoImageType;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelFlowType extends AbstractType
{
    private $mbhFormBuilder;
    private $router;

    public function __construct(MBHFormBuilder $mbhFormBuilder, Router $router) {
        $this->mbhFormBuilder = $mbhFormBuilder;
        $this->router = $router;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Hotel $hotel */
        $hotel = $builder->getData();

        switch ($options['flow_step']) {
            case 1:
                $this->mbhFormBuilder->addMultiLangField($builder, TextType::class, 'fullTitle', [
                    'group' => 'form.hotelType.general_info',
                    'attr' => ['placeholder' => 'form.hotelType.placeholder_my_hotel'],
                    'label' => 'form.hotelType.name'
                ]);
                break;
            case 2:
                $this->mbhFormBuilder->addMultiLangField($builder, TextareaType::class, 'description', [
                    'attr' => ['class' => 'tinymce'],
                    'label' => 'form.hotelType.description',
                    'group' => 'no-group',
                    'required' => false
                ]);
                break;
            case 3:
                $builder->add('logoImage', LogoImageType::class, [
                    'label' => 'form.hotel_logo.image_file.help',
                    'group' => 'form.hotelType.settings',
                    'required' => false,
                    'logo_image_download_url' => $this->getDownloadUrl($hotel->getLogoImage()),
                    'logo_image_delete_url' => $this->router->generate('hotel_delete_logo_image', [
                        'id' => $hotel->getId(),
                        'redirect_url' => $this->router->generate('mb_flow', ['type' => HotelFlow::FLOW_TYPE])
                    ])
                ]);
                break;
            case 6:
                $builder->add('contactInformation', ContactInfoType::class, [
                    'group' => 'no-group',
                    'hasGroups' => false
                ]);
                break;
            case 7:
                $builder->add('defaultImage', LogoImageType::class, [
                    'label' => 'Главная',
                    'group' => 'form.hotelType.settings',
                    'required' => false,
                    'logo_image_download_url' => $this->getDownloadUrl($hotel->getDefaultImage()),
                    'showHelp' => false
                ]);
                break;
            default:
                throw new \InvalidArgumentException('Incorrect flow step number!');
        }
    }

    /**
     * @param Image|null $image
     * @return null|string
     */
    private function getDownloadUrl(?Image $image)
    {
        return !is_null($image)
            ? $this->router->generate('hotel_logo_download', ['id' => $image->getId()])
            : null;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($view->children['contactInformation'])) {
            $view->children['contactInformation']->vars['embedded'] = true;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Hotel::class
        ]);
    }
}