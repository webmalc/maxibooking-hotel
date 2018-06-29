<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class ChannelManagerConfigType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ChannelManagerConfigInterface $config */
        $config = $builder->getData();
        if (!is_null($config) && $config->isReadyToSync()) {
            $builder
                ->add(
                    'isEnabled',
                    CheckboxType::class,
                    [
                        'label' => 'form.channel_manager_config_type.is_included',
                        'value' => true,
                        'required' => false,
                        'help' => 'form.channel_manager_config_type.should_we_use_in_channel_manager'
                    ]
                );
        }

        $builder
            ->add(
                'hotelId', TextType::class, [
                    'label' => 'form.channel_manager_config_type.hotel_id',
                    'required' => true,
                    'attr' => ['placeholder' => 'hotel id'],
                    'help' => $this->translator
                        ->trans('form.channel_manager_config_type.hotel_id_in.help', ['%cmName%' => $options['channelManagerName']])
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'channelManagerName' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_channel_manager_config_type';
    }
}
