<?php

namespace MBH\Bundle\HotelBundle\Form\HotelFlow;

use MBH\Bundle\BaseBundle\Service\MBHFormBuilder;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Form\ContactInfoType;
use MBH\Bundle\HotelBundle\Form\HotelLogoImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelFlowType extends AbstractType
{
    private $mbhFormBuilder;

    public function __construct(MBHFormBuilder $mbhFormBuilder) {
        $this->mbhFormBuilder = $mbhFormBuilder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                    'group' => 'form.hotelType.general_info',
                    'required' => false
                ]);
                break;
            case 3:
                $builder->add('logoImage', HotelLogoImageType::class, [
                    'label' => 'form.hotel_logo.image_file.help',
                    'group' => 'form.hotelType.settings',
                    'required' => false,
                    'hotel' => $builder->getData()
                ]);
                break;
            case 4:
                $this->mbhFormBuilder->addMergedFormFields($builder, HotelAddressType::class, $builder->getData());
                break;
            case 5:
                $this->mbhFormBuilder->addMergedFormFields($builder, HotelLocationType::class, $builder->getData());
                break;
            case 6:
                $builder->add('contactInformation', ContactInfoType::class);
                break;
            default:
                throw new \InvalidArgumentException('Incorrect flow step number!');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Hotel::class
        ]);
    }
}