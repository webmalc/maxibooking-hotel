<?php

namespace MBH\Bundle\ChannelManagerBundle\Form\TripAdvisor;

use MBH\Bundle\ChannelManagerBundle\Document\Room;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorRoomType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TripAdvisorRoomTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Room $channelManagerRoomType */
        $channelManagerRoomType = $options['roomTypes'][$builder->getName()];
        $roomType = $channelManagerRoomType->getRoomType();

        $builder
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'form.trip_advisor_tariff_type.isSynchronized.label',
                'help' => 'form.trip_advisor_tariff_type.isSynchronized.help',
                'required' => false,
                'group' => $roomType->getName(),
                'attr' => [
                    'disabled' => !empty($options['requiredFieldsErrors'][$builder->getName()])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TripAdvisorRoomType::class,
                'hotel' => null,
                'roomTypes' => null,
                'requiredFieldsErrors' => null
            ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['unfilledFields'] = $options['requiredFieldsErrors'][$view->vars['name']];
    }

    public function getBlockPrefix()
    {
        return 'mbhchannel_manager_bundle_trip_advisor_room_type';
    }
}
