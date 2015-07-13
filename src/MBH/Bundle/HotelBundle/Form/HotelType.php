<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HotelType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $logoHelp = 'Загрузите файл';
        if($options['imageUrl']) {
            $logoHelp = '<a href="'.$options['imageUrl'].'" class="fancybox">Просмотреть изображение</a></br><a class="text-danger" href="'.$options['removeImageUrl'].'"><i class="fa fa-trash-o"></i> Удалить</a>';
        }

        $builder
            ->add('fullTitle', 'text', [
                'label' => 'form.hotelType.name',
                'group' => 'form.hotelType.general_info',
                'required' => true,
                'attr' => ['placeholder' => 'form.hotelType.placeholder_my_hotel']
            ])
            ->add('title', 'text', [
                'label' => 'form.hotelType.inner_name',
                'group' => 'form.hotelType.general_info',
                'required' => false,
                'attr' => ['placeholder' => 'form.hotelType.placeholder_hotel'],
                'help' => 'form.hotelType.maxibooking_inner_name'
            ])
            ->add('prefix', 'text', [
                'label' => 'form.hotelType.prefix',
                'group' => 'form.hotelType.general_info',
                'required' => false,
                'attr' => ['placeholder' => 'HTL'],
                'help' => 'form.hotelType.document_use_name'
            ])
            ->add('file', 'file', [
                'label' => 'form.hotelType.logo',
                'group' => 'form.hotelType.settings',
                'help' => $logoHelp,
                'required' => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Image()
                ]
            ])
            /*->add('isHostel', 'checkbox', [
                'label' => 'form.hotelType.hostel',
                'group' => 'form.hotelType.settings',
                'value' => true,
                'required' => false,
                'help' => 'form.hotelType.hostel_hotel_or_not'
            ])*/
            ->add('isDefault', 'checkbox', [
                'label' => 'form.hotelType.is_default',
                'group' => 'form.hotelType.settings',
                'value' => true,
                'required' => false,
                'help' => 'form.hotelType.is_default_maxibooking'
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
            'types' => [],
            'imageUrl' => null,
            'removeImageUrl' => null,
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_hotelbundle_hoteltype';
    }

}
