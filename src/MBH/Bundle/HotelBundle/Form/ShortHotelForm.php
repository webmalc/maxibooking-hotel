<?php

namespace MBH\Bundle\HotelBundle\Form;

use MBH\Bundle\BaseBundle\Service\MBHFormBuilder;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShortHotelForm extends AbstractType
{
    private $mbhFormBuilder;

    public function __construct(MBHFormBuilder $mbhFormBuilder) {
        $this->mbhFormBuilder = $mbhFormBuilder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->mbhFormBuilder->addMultiLangField($builder, TextType::class, 'fullTitle', [
            'group' => 'form.hotelType.general_info',
            'attr' => ['placeholder' => 'form.hotelType.placeholder_my_hotel'],
            'label' => 'form.hotelType.name'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Hotel::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_short_hotel_form';
    }
}
