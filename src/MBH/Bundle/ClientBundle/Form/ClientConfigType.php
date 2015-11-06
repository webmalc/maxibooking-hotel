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
                'group' => 'form.clientConfigType.main_group',
                'value' => true,
                'required' => false,
                'help' => 'form.clientConfigType.is_sms_notification_turned_on'
            ])
            ->add('useRoomTypeCategory', 'checkbox', [
                'label' => 'form.clientConfigType.is_disabled_room_type_category',
                'group' => 'form.clientConfigType.main_group',
                'required' => false,
            ])
            ->add('searchDates', 'text', [
                'label' => 'form.clientConfigType.search_dates',
                'help' => 'form.clientConfigType.search_dates_desc',
                'group' => 'form.clientConfigType.search_group',
                'required' => true,
            ])
            ->add('searchWindows', 'checkbox', [
                'label' => 'form.clientConfigType.search_windows',
                'help' => 'form.clientConfigType.search_windows_desc',
                'group' => 'form.clientConfigType.search_group',
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
