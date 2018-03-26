<?php

namespace MBH\Bundle\UserBundle\Form;

use MBH\Bundle\BillingBundle\Lib\Model\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientContactsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.client_contacts_type.name.label'
            ])
            ->add('email', TextType::class, [
                'attr' => [
                    'readonly' => 'true'
                ],
                'label' => 'form.client_contacts_type.email.label'
            ])
            ->add('phone', TextType::class, [
                'label' => 'form.client_contacts_type.phone.label'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhuser_bundle_client_contacts_type';
    }
}
