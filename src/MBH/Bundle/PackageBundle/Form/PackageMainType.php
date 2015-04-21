<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

class PackageMainType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf('%02d', $i).':00';
        }
        for ($i = 0; $i <= 10; $i++) {
            $places[$i] = $i;
        }



        $builder
            ->add('begin', 'date', array(
                'label' => 'Заезд',
                'group' => 'Заезд/отъезд',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'error_bubbling' => true,
                'attr' => array('class' => 'datepicker begin-datepiker input-small', 'data-date-format' => 'dd.mm.yyyy'),
            ))
            ->add('end', 'date', array(
                'label' => 'Отъезд',
                'group' => 'Заезд/отъезд',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'error_bubbling' => true,
                'attr' => array('class' => 'datepicker end-datepiker input-small', 'data-date-format' => 'dd.mm.yyyy'),
            ))
            ->add('roomType', 'document', [
                'label' => 'Тип номера',
                'class' => 'MBHHotelBundle:RoomType',
                'group' => 'Номер',
                'query_builder' => function(DocumentRepository $dr) use ($options) {
                    return $dr->createQueryBuilder('q')
                        ->field('hotel.id')->equals($options['hotel']->getId())
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc'])
                        ;
                },
                'required' => true
            ])
            ->add(
                'adults',
                'choice',
                [
                    'label' => 'Взрослых',
                    'group' => 'Номер',
                    'required' => true,
                    'group' => 'Номер',
                    'multiple' => false,
                    'choices' => $places,
                    'attr' => array('class' => 'input-xxs'),
                ]
            )
            ->add(
                'children',
                'choice',
                [
                    'label' => 'Детей',
                    'group' => 'Номер',
                    'required' => true,
                    'group' => 'Номер',
                    'multiple' => false,
                    'choices' => $places,
                    'attr' => array('class' => 'input-xxs'),
                ]
            )
            ->add(
                'isSmoking',
                'checkbox',
                [
                    'label' => 'Курящий?',
                    'required' => false,
                    'group' => 'Номер',
                ]
            );

        if ($options['price']) {
            $builder->add(
                'price',
                'text',
                [
                    'label' => 'form.packageMainType.price',
                    'required' => true,
                    'group' => 'Цена',
                    'error_bubbling' => true,
                    'property_path' => 'packagePrice',
                    'attr' => [
                        'class' => 'price-spinner'
                    ],
                ]
            );
        }

        $builder
            ->add(
                'discount',
                'text',
                [
                    'label' => 'form.packageMainType.discount',
                    'required' => false,
                    'group' => 'Цена',
                    'attr' => [
                        'class' => 'discount-spinner'
                    ],
                ]
            )
            ->add(
                'arrivalTime',
                'choice',
                [
                    'label' => 'form.packageMainType.check_in_time',
                    'required' => false,
                    'group' => 'Заезд/отъезд',
                    'multiple' => false,
                    'attr' => array('class' => 'input-xs'),
                ]
            )
            ->add(
                'departureTime',
                'choice',
                [
                    'label' => 'form.packageMainType.check_out_time',
                    'required' => false,
                    'group' => 'Заезд/отъезд',
                    'multiple' => false,
                    'choices' => $hours,
                    'attr' => array('class' => 'input-xs'),
                ]
            )
            ->add(
                'purposeOfArrival',
                'choice',
                [
                    'label' => 'form.packageMainType.arrival_purpose',
                    'required' => false,
                    'group' => 'Информация',
                    'multiple' => false,
                    'choices' => $options['arrivals'],
                ]
            )
            ->add(
                'note',
                'textarea',
                [
                    'label' => 'form.packageMainType.comment',
                    'group' => 'Информация',
                    'required' => false,
                ]
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
                'arrivals' => [],
                'defaultTime' => null,
                'price' => false,
                'hotel' => null
            )
        );
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_main_type';
    }

}
