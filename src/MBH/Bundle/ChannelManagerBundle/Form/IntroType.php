<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class IntroType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hotelId', TextType::class, [
                'label' => 'messages_store.hotel_id',
                'required' => true
            ])
            ->add('hotelName', TextType::class, [
                'mapped' => false,
                'attr' => ['readonly' => 'readonly'],
                'required' => false,
                'label' => 'messages_store.hotel_name',
                'data' => $options['hotelName'],
                'help' => $this->translator->trans('channel_manager.intro_type.hotel_data.help', [
                    '%nameRoute%' => $options['hotelNameFormRoute']
                ])
            ])
            ->add('hotelAddress', TextType::class, [
                'mapped' => false,
                'attr' => ['readonly' => 'readonly'],
                'required' => false,
                'label' => 'messages_store.hotel_address',
                'data' => $options['hotelAddress'],
                'help' => $this->translator->trans('channel_manager.intro_type.hotel_data.help', [
                    '%nameRoute%' => $options['hotelAddressFormRoute']
                ])
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'hotelAddress' => null,
            'hotelAddressFormRoute' => null,
            'hotelName' => null,
            'hotelNameFormRoute' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_intro_type';
    }
}
