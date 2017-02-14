<?php

namespace MBH\Bundle\OnlineBookingBundle\Form;

use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OnlineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hotel', 'document', [
                'class' => Hotel::class
            ])
            ->add('roomType', 'text')
            ->add('begin', 'date', [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'),
            ])
            ->add('end', 'date', [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'),
            ])
            ->add('adults', 'number')
            ->add('children', 'number')
        ;
    }

    /*public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
        ]);
    }*/


    public function getName()
    {
        return 'online';
    }
}