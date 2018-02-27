<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ExpediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //TODO: Изменить подписи
        $builder
            ->add('isEnabled', CheckboxType::class, [
                    'label' => 'form.expedia.isEnabled',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.expedia.should_we_use_in_channel_manager'
                ]
            )
            ->add('hotelId', IntegerType::class,[
                'label' => 'form.expedia.hotel_id',
                'required' => true,
                'help' => 'form.expedia.hotel_id.help',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\ChannelManagerBundle\Document\ExpediaConfig',
        ));
    }

    public function getName()
    {
        return 'mbhchannel_manager_bundle_expedia_type';
    }
}
