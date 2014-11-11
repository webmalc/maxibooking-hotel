<?php

namespace MBH\Bundle\ClientBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClientConfigType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'isSendSms',
                'checkbox',
                [
                    'label' => 'Смс оповещение?',
                    'value' => true,
                    'required' => false,
                    'help' => 'Включено ли смс оповещение клиентов?'
                ]
            )
           ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\ClientBundle\Document\ClientConfig',
            )
        );
    }

    public function getName()
    {
        return 'mbh_bundle_clientbundle_client_config_type';
    }

}
