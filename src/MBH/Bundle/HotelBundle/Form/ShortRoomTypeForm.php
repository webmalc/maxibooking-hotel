<?php

namespace MBH\Bundle\HotelBundle\Form;

use MBH\Bundle\BaseBundle\Service\MBHFormBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShortRoomTypeForm extends AbstractType
{
    private $mbhFormBuilder;

    public function __construct(MBHFormBuilder $mbhFormBuilder) {
        $this->mbhFormBuilder = $mbhFormBuilder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->mbhFormBuilder->addMultiLangField($builder, TextType::class, 'fullTitle', [
            'group' => 'form.roomTypeType.general_info',
            'attr' => ['placeholder' => 'form.roomTypeType.comfort_plus'],
            'label' => 'form.roomTypeType.name',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => \MBH\Bundle\HotelBundle\Document\RoomType::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_short_room_type_form';
    }
}
