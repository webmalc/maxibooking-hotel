<?php

namespace MBH\Bundle\ClientBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClientConfigType
 */
class ClientConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isSendSms', 'checkbox', [
                'label' => 'form.clientConfigType.sms_notification',
                'value' => true,
                'required' => false,
                'help' => 'form.clientConfigType.is_sms_notification_turned_on'
            ])
            ->add('isDisabledRoomTypeCategory', 'checkbox', [
                'label' => 'form.clientConfigType.is_disabled_room_type_category',
                //'value' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\ClientBundle\Document\ClientConfig'
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_clientbundle_client_config_type';
    }

}
