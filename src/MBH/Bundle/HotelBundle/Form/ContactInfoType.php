<?php

namespace MBH\Bundle\HotelBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'form.contact_info_type.full_name.label',
                'help' => 'form.contact_info_type.full_name.help',
                'required' => false,
                'group' => 'form.hotel_contact_information.contact_info.group'
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'form.contact_info_type.phone.label',
                'help' => 'form.contact_info_type.phone.help',
                'required' => false,
                'group' => 'form.hotel_contact_information.contact_info.group'
            ])
            ->add('email', TextType::class, [
                'label' => 'form.contact_info_type.email.label',
                'help' => 'form.contact_info_type.email.help',
                'required' => false,
                'group' => 'form.hotel_contact_information.contact_info.group'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\ContactInfo'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhhotel_bundle_contact_info_type';
    }
}
